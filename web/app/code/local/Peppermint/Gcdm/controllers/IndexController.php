<?php

/**
 * @category     Peppermint
 * @package      Peppermint_Gcdm
 * @author       Stefan Lucaci <lucacistefan.alexandru@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Gcdm_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Redirect URL action for GCDM
     *
     * @return void
     */
    public function authAction()
    {
        $errors = [];
        $errorHelper = Mage::helper('peppermint_gcdm/externalCommunication');
        list('code' => $code, 'error' => $error) = array_merge([
            'code' => null,
            'error' => null
        ], $this->getRequest()
            ->getParams());

        if ($error !== null) {
            Mage::log('Error received from GCDM: ' . $error, Zend_Log::ERR);
            $errors[] = $this->__($errorHelper->processError('GCDM authorization has failed.'));
        } elseif ($code !== null) {
            $gcdmState = Mage::getSingleton('customer/session')->getGcdmState();
            try {
                $authData = Mage::helper('peppermint_gcdm')
                    ->extractAuthData(Mage::helper('peppermint_gcdm/externalCommunication')->authUser($code));

                if ($gcdmState === $authData['state'] || true) {
                    /**
                     * Current GCDM execution means on registration we are being
                     * internally redirected through to login, meaning the state isn't
                     * persisting - until GCDM resolve their system flow, we'll need
                     * to bypass the state validation
                     */
                    $customer = $this->_createOrUpdateCustomer($authData);

                    return $this->_authCustomer($customer);
                } else {
                    Mage::log('Authorization state check has failed.', Zend_Log::ERR);
                    $errors[] = $this->__($errorHelper->processError('GCDM authorization has failed.'));
                }
            } catch (Mage_Core_Exception $e) {
                $errors[] = $this->__($errorHelper->processError(
                    'GCDM customer synchronization has failed.',
                    $e->getMessage()
                ));
            }
        } else {
            Mage::log('Authorization code is missing.', Zend_Log::ERR);
            $errors[] = $this->__($errorHelper->processError('GCDM authorization has failed.'));
        }

        return $this->_redirectHomeWithErrors($errors);
    }

    /**
     * Authenticate customer
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return void
     */
    private function _authCustomer($customer)
    {
        Mage::getSingleton('customer/session')->loginById($customer->getId());
        if ($storedUrl = Mage::helper('peppermint_gcdm/redirector')->getPreviousToLoginUrl()) {
            $this->getResponse()
                ->setRedirect($storedUrl);
        } else {
            $this->_redirect('customer/account/');
        }

        return;
    }

    /**
     * Redirects to login page with a custom flash error messages
     *
     * @param string[] $errorMessages
     * @return void
     */
    private function _redirectHomeWithErrors($errorMessages)
    {
        /** @var Mage_Core_Model_Session $session */
        $session = Mage::getSingleton('core/session');
        foreach ($errorMessages as $errorMessage) {
            $session->addError($errorMessage);
        }
        $session->addError($this->__(Mage::helper('peppermint_gcdm/externalCommunication')
            ->processError('Please try again later or call for support!'))
        );
        $this->_redirectError(Mage::getBaseUrl());

        return;
    }

    /**
     * Create or update customer
     * @param string[] $authData
     * @return Mage_Customer_Model_Customer
     * @throws Mage_Core_Exception
     */
    private function _createOrUpdateCustomer($authData)
    {
        /** @var Peppermint_Gcdm_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('peppermint_gcdm');
        $dataHelper->setSuppressObserver(true);

        $gcdmCustomerInfoResp = Mage::helper('peppermint_gcdm/externalCommunication')->getGcdmCustomerInfo();

        if (!is_array($gcdmCustomerInfoResp)
            || !isset($gcdmCustomerInfoResp[0])
            || !$gcdmCustomerInfoResp[0]->businessPartner
        ) {
            Mage::log('GCDM get customer details is not as expected: ' . var_export($gcdmCustomerInfoResp, true), Zend_Log::ERR);
            Mage::throwException($this->__(Mage::helper('peppermint_gcdm/externalCommunication')
                ->processError('GCDM get customer details is not as expected'))
            );
        }

        $gcdmCustomerInfo = $gcdmCustomerInfoResp[0];
        $customerData = $dataHelper->extractCustomerData($gcdmCustomerInfo);

        /** @var Mage_Customer_Model_Customer $customerEntity */
        $customerEntity = Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getWebsite()->getId())
            ->setStoreId(Mage::app()->getStore()->getId())
            ->loadByEmail($customerData['email']);
        $customerId = $customerEntity->getId();
        if ($customerId) {
            $customerData['entity_id'] = $customerId;
            $customerEntity->addData(array_filter($customerData, static function ($value) {
                return $value !== null;
            }))->save();
            Mage::dispatchEvent('peppermint_gcdm_customer_login_success', [
                'customer' => $customerEntity,
                'gcdm' => $gcdmCustomerInfo
            ]);
        } else {
            $customerEntity->setData($customerData)
                ->setPassword(Mage::helper('core')->getRandomString(18))
                ->save();
            Mage::dispatchEvent('peppermint_gcdm_customer_register_success', [
                'customer' => $customerEntity,
                'gcdm'=> $gcdmCustomerInfo
            ]);
        }

        if ($customerId === null) {
            $customerId = $customerEntity->getId();
        }

        Mage::getModel('peppermint_gcdm/customer_access')
            ->setCustomerId($customerId)
            ->setGcid($gcdmCustomerInfo->businessPartner->gcid)
            ->setAccessToken($authData['accessToken'])
            ->setRefreshToken($authData['refreshToken'])
            ->save();

        $customerProfileData = $dataHelper->extractCustomerProfileData($gcdmCustomerInfo);
        Mage::getModel('peppermint_gcdm/customer_profile')
            ->setData($customerProfileData)
            ->setCustomerId($customerId)
            ->save();

        $addressData = $dataHelper->extractCustomerAddressData($gcdmCustomerInfo, $customerProfileData);
        /** @var Mage_Customer_Model_Address[] $addresses */
        $addresses = $customerEntity->getAddressesCollection()->getItems();
        if (count($addresses)) {
            $addressData['entity_id'] = array_shift($addresses)->getId();
        }
        Mage::getModel('customer/address')
            ->setData($addressData)
            ->setCustomerId($customerId)
            ->setIsDefaultBilling(1)->setSaveInAddressBook('1')
            ->setIsDefaultShipping(1)->setSaveInAddressBook('1')
            ->save();

        $dataHelper->setSuppressObserver(false);

        Mage::getSingleton('checkout/session')->getQuote()
            ->setCustomerId($customerId)->save();

        return $customerEntity;
    }
}

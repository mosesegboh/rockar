<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Gcdm
 * @author    Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Gcdm_Model_Observer
{
    /**
     * @var GCDM cache data
     */
    protected $_data;

    /**
     * Interceptor for customer update.
     *
     * @param Varien_Event_Observer $observer
     * @return Peppermint_Gcdm_Model_Observer
     */
    public function onBeforeCustomerSave(Varien_Event_Observer $observer)
    {
        /** @var Peppermint_Gcdm_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('peppermint_gcdm');

        if (!$dataHelper->getSuppressObserver()) {
            try {
                $requestData = Mage::app()->getRequest()->getParams();

                if ($this->_data !== $requestData) {
                    $customerId = $observer->getData('customer')
                        ->getId();
                    $gcdmCustomerAccess = Mage::getModel('peppermint_gcdm/customer_access')->load($customerId);
                    $helper = Mage::helper('peppermint_gcdm/externalCommunication')
                            ->setAccessToken($gcdmCustomerAccess->getAccessToken())
                            ->setRefreshToken($gcdmCustomerAccess->getRefreshToken());

                     // Need to refresh GUIDs data before making a request to GCDM
                    $this->_updateCustomerProfileIdentifiers($customerId, $dataHelper, $helper);

                    $gcdmCustomerProfile = Mage::getModel('peppermint_gcdm/customer_profile')->load($customerId);
                    $gcdmData = [
                        'gcid' => $gcdmCustomerAccess->getGcid(),
                        'main_address_guid' => $gcdmCustomerProfile->getMainAddressGuid(),
                        'main_phone_guid' => $gcdmCustomerProfile->getMainPhoneGuid(),
                        'business_address_guid' => $gcdmCustomerProfile->getBusinessAddressGuid(),
                        'business_phone_guid' => $gcdmCustomerProfile->getBusinessPhoneGuid(),
                        'vat_number_guid' => $gcdmCustomerProfile->getVatNumberGuid()
                    ];

                    // When request is from checkout
                    $data = Mage::app()->getRequest()->getRouteName() === 'checkout'
                        ? $dataHelper->prepareDataForGcdmFromCheckout($gcdmData, $requestData)
                        : $dataHelper->prepareDataForGcdm($gcdmData, $requestData);

                    if ($data) {
                        $helper->putGcdmCustomerInfo($data);
                        $this->_updateCustomerProfileIdentifiers($customerId, $dataHelper, $helper);
                    }

                    $this->_data = $requestData;
                }
            } catch (Mage_Core_Exception $e) {
                Mage::throwException($dataHelper->__(
                    Mage::helper('peppermint_gcdm/externalCommunication')->processError(
                        'GCDM communication or validation has failed!',
                        $e->getMessage()
                    )
                ));
            }
        }

        return $this;
    }

    /**
     * Check data is has change before sending to GCDM to prevent repeated calls with the same data to GCDM.
     * As customer model before save function is also being called when saving using customer/address model.
     *
     * @param array $newData
     * @return boolean
     */
    protected function _hasRequestDataChange($newData)
    {
        return $newData !== $this->_data;
    }

    /**
     * Update customer profiles communication preference.
     *
     * @param int|string $customerId
     * @param Peppermint_Gcdm_Helper_Data $helper
     * @param Peppermint_Gcdm_Helper_ExternalCommunication $apiHelper
     * @return void
     */
    private function _updateCustomerProfileIdentifiers($customerId, $helper, $apiHelper)
    {
        $gcdmCustomerInfoResp = $apiHelper->getGcdmCustomerInfo();
        $gcdmResponseData = reset($gcdmCustomerInfoResp);

        if (!$gcdmResponseData) {
            Mage::log('GCDM get customer details is not as expected: ' . var_export($gcdmCustomerInfoResp, true), Zend_Log::ERR);
            Mage::throwException($helper->__(Mage::helper('peppermint_gcdm/externalCommunication')
                ->processError('GCDM get customer details is not as expected'))
            );
        }

        $helper->syncGuidData($gcdmResponseData, $customerId);
    }

    /**
     * Interceptor on preDispatch for customer/account/login.
     *
     * @param Varien_Event_Observer $observer
     * @return Peppermint_Gcdm_Model_Observer
     */
    public function redirectToExternalLogin(Varien_Event_Observer $observer)
    {
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            $observer->getData('controller_action')
                ->getResponse()
                ->setRedirect(
                    Mage::helper('peppermint_gcdm/redirector')->rememberPreviousUrl()
                        ->getRedirectUrl()
                );
        }

        return $this;
    }

    /**
     * Interceptor for customer logout.
     *
     * @param Varien_Event_Observer $observer
     * @return Peppermint_Gcdm_Model_Observer
     */
    public function onCustomerLogout(Varien_Event_Observer $observer)
    {
        $gcdmCustomerAccess = Mage::getModel('peppermint_gcdm/customer_access')->load(
            $observer->getData('customer')
                ->getId()
        );

        try {
            Mage::helper('peppermint_gcdm/popup')->clearCustomerSessionActivationStatus();

            Mage::helper('peppermint_gcdm/externalCommunication')
                ->setAccessToken($gcdmCustomerAccess->getAccessToken())
                ->setRefreshToken($gcdmCustomerAccess->getRefreshToken())
                ->revokeGcdmToken();
        } catch (Mage_Core_Exception $e) {
            Mage::log('GCDM communication or validation has failed!', Zend_Log::ERR);
        }

        return $this;
    }

    /**
     * Interceptor for re authenticate via refresh token.
     *
     * @param Varien_Event_Observer $observer
     * @return Peppermint_Gcdm_Model_Observer
     */
    public function onGcdmReAuth(Varien_Event_Observer $observer)
    {
        /** @var Peppermint_Gcdm_Helper_ExternalCommunication $gcdmExtHelper */
        $gcdmExtHelper = $observer->getGcdm();
        /** @var Rockar_Customer_Model_Session $customerSession */
        $customerSession = Mage::getSingleton('customer/session');

        if ($gcdmExtHelper) {
            try {
                Mage::getModel('peppermint_gcdm/customer_access')->load($customerSession->getCustomerId())
                    ->setAccessToken($gcdmExtHelper->getAccessToken())
                    ->setRefreshToken($gcdmExtHelper->getRefreshToken())
                    ->save();
            } catch (Mage_Core_Exception $e) {
                Mage::log('GCDM refresh token failed', Zend_Log::ERR);
            }
        } else {
            $customerSession->logout();
        }

        return $this;
    }

    /**
     * Prepare customer policy popup
     *
     * @param Varien_Event_Observer $observer
     * @return Peppermint_Gcdm_Model_Observer
     */
    public function processCustomerCurrentPolicy(Varien_Event_Observer $observer)
    {
        $gcdmInfo = $observer->getEvent()->getGcdm();
        $gcdmHelper = Mage::helper('peppermint_gcdm');
        $customerPolicies = $gcdmInfo->contactPolicyConsents ?? [];
        $customerSession = Mage::getSingleton('customer/session');
        try {
            $latestPolicy = Mage::helper('peppermint_gcdm/externalCommunication')->getCurrentPolicy();
            $lastPolicyAccepted = $this->_checkCustomerPolicy($customerPolicies, $latestPolicy);
        } catch (Mage_Core_Exception $exception) {
            // don't show the popup if policy endpoint is down
            $lastPolicyAccepted = true;
        }

        $customerSession->setData($gcdmHelper::GCDM_LATEST_POLICY_ACCEPTED_SESSION_KEY, $lastPolicyAccepted);
        $customerSession->unsetData($gcdmHelper::GCDM_LATEST_POLICY_SESSION_KEY);
        $customerSession->unsetData($gcdmHelper::GCDM_LATEST_POLICY_CHECKBOXES_SESSION_KEY);

        if (!$lastPolicyAccepted) {
            $gcdmHelper->storePolicyDataToSession($latestPolicy);
        }

        return $this;
    }

    /**
     * Store customer mandatory details in session
     *
     * @param Varien_Event_Observer $observer
     */
    public function checkMissingDetails(Varien_Event_Observer $observer)
    {
        $data = $observer->getEvent()
            ->getGcdm()
            ->businessPartner;

        Mage::getSingleton('customer/session')->setData(
            Peppermint_Gcdm_Helper_Data::GCDM_CUSTOMER_MANDATORY_DETAILS_SESSION_KEY,
            [
                'surname' => trim($data->surname ?? ''),
                'givenName' => trim($data->givenName ?? ''),
                'salutation' => trim($data->salutation ?? '')
            ]
        );
    }

    /**
     * Check whether the customer accepted the latest policy
     *
     * @param stdClass[] $customerPolicies
     * @param stdClass $currentPolicy
     * @return bool
     */
    protected function _checkCustomerPolicy($customerPolicies, $currentPolicy): bool
    {
        foreach ($customerPolicies as $customerPolicy) {
            if ($customerPolicy->policyId === $currentPolicy->name && $customerPolicy->country === $currentPolicy->country) {
                return $customerPolicy->majorVersion === $currentPolicy->majorVersion;
            }
        }

        return false;
    }
}

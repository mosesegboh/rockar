<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Gcdm
 * @author    Sergejs Plisko <techteam@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Gcdm_PopupController extends Mage_Core_Controller_Front_Action
{
    /**
     * Send GCDM activation email and logout customer
     *
     * @return void
     */
    public function applyAction()
    {
        $response = [
            'success' => true,
            'error_message' => '',
            'redirect' => ''
        ];

        $action = $this->getRequest()->getParam('gcdm_submit');

        if ($action === 'email_sent') {
            $customerEmail = $this->getCustomerSession()->getCustomer()->getEmail();
            $gcdmResponese = Mage::helper('peppermint_gcdm/externalCommunication')
                ->resendMailActivationCode($customerEmail);

            if ($decodeRespone = json_decode($gcdmResponese, true)) {
                $response['success'] = false;
                $response['error_message'] = $decodeRespone['message'];
            }
        }

        if ($response['success'] === true) {
            $response['redirect'] = Mage::getUrl();
            $this->getCustomerSession()->logout();
        }

        $this->getResponse()->setHeader(Zend_Http_Client::CONTENT_TYPE, 'application/json');
        $this->getResponse()->setBody(json_encode($response));
    }

    /**
     * Submit policies and log customer out
     *
     * @return void
     */
    public function submitAction()
    {
        $response = [
            'success' => true,
            'logout' => false,
            'redirect' => ''
        ];

        if ($this->getRequest()->getParam('logout')) {
            $response['logout'] = true;
            $response['redirect'] = Mage::getUrl();
            $this->getCustomerSession()->logout();
        } else {
            try {
                $gcdmResponse = Mage::helper('peppermint_gcdm')->processPolicies(
                    (array)$this->getRequest()->getParam('policy_submit')
                );

                if ($gcdmResponse->message) {
                    $response['success'] = false;
                    $response['error_message'] = $gcdmResponse->message;
                } elseif (isset($gcdmResponse->httpStatus) && $gcdmResponse->httpStatus !== 200) {
                    $response['success'] = false;
                    if (isset($gcdmResponse->description)) {
                        $response['error_message'] = $gcdmResponse->description;
                    }
                } else {
                    Mage::getSingleton('customer/session')
                        ->setData(Peppermint_Gcdm_Helper_Data::GCDM_LATEST_POLICY_ACCEPTED_SESSION_KEY, true);
                }
            } catch (Exception $e) {
                $response['success'] = false;
                $response['error_message'] = $e->getMessage();
            }
        }

        $this->getResponse()->setHeader(Zend_Http_Client::CONTENT_TYPE, 'application/json');
        $this->getResponse()->setBody(json_encode($response));
    }

    /**
     * Get customer session
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Submit missing details to DSP and GCDM
     */
    public function submitMissingDetailsAction()
    {
        $response = [
            'success' => true,
            'logout' => false,
            'redirect' => ''
        ];

        if ($this->getRequest()->getParam('logout')) {
            $response['logout'] = true;
            $response['redirect'] = Mage::getUrl();
            $this->getCustomerSession()->logout();
        } else {
            try {
                if ($this->getRequest()->isPost()) {
                    $session = Mage::getSingleton('customer/session');
                    /**
                     * @var Mage_Customer_Model_Customer
                     */
                    $customer = $session->getCustomer();
                    $customerData = $this->getRequest()->getParams();

                    // Save Customer Data
                    $customer->addData([
                        'firstname' => $customerData['customer']['firstname'],
                        'lastname' => $customerData['customer']['lastname'],
                        'prefix' => $customerData['customer']['prefix']
                    ])->save();

                    $session->setData(
                        Peppermint_Gcdm_Helper_Data::GCDM_CUSTOMER_MANDATORY_DETAILS_SESSION_KEY,
                        [
                            'surname' => $customerData['customer']['lastname'],
                            'givenName' => $customerData['customer']['firstname'],
                            'salutation' => $customerData['customer']['prefix']
                        ]
                    );
                }
            } catch (Mage_Core_Exception $e) {
                $response = [
                    'success' => false,
                    'error_message' => $e->getMessage(),
                    'logout' => true
                ];
            } catch (Exception $e) {
                $response = [
                    'success' => false,
                    'error_message' => $this->__('There was an error with form submit.'),
                    'logout' => true
                ];

                Mage::logException($e);
            }
        }

        $this->getResponse()->setHeader(Zend_Http_Client::CONTENT_TYPE, 'application/json');
        $this->getResponse()->setBody(json_encode($response));
    }
}

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Checkout
 * @author    Mihai Chezan <mihai.chezan@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

require_once Mage::getModuleDir('controllers', 'Rockar2_Checkout') . DS . 'OnepageController.php';

class Peppermint_Checkout_OnepageController extends Rockar2_Checkout_OnepageController
{
    /**
     * Error code for out of stock
     */
    const EXCEPTION_OUT_OF_STOCK = 800;

    /**
     * Error code for out of stock
     */
    const EXCEPTION_ALREADY_PROCESSING = 900;

    /**
     * Error code for out of stock
     */
    const EXCEPTION_PLACING_ORDER = 1000;

    /**
     * Max waiting cycles, 5 s each, for the parallel order creation check, up to 2 mins
     * (as long as reservation stays)
     */
    const RESERVATION_WAIT_RETRIES = 24;

    /**
     * @var null|int Trace record that reserves vehicle for the customer
     */
    protected $_reservationRecordId = null;

    /**
     * Overridden to replace product if not available
     *
     * {@inheritDoc}
     */
    public function indexAction()
    {
        $quote = $this->getOnepage()->getQuote();
        $items = $quote->getItemsCollection()->getItems();

        // check if quote has any items
        if ($items) {
            $isProductAvailable = Mage::helper('peppermint_checkout/ReplacementProduct')->checkProductLeadTime();

            if (!$isProductAvailable) {
                $result = new Varien_Object(['success' => false]);
                // try to replace product
                Mage::dispatchEvent(
                    'peppermint_checkout_onepage_controller_before_index_action',
                    [
                        'result' => $result,
                        'quote'  => $quote
                    ]
                );

                if (!$result->getSuccess()) {
                    // if product can't be replaced
                    Mage::getSingleton('checkout/session')->addError($this->__('Product no longer available'));
                    $this->_redirect('checkout/cart');
                    return;
                }

                // if product was replaced restart checkout process
                $this->_redirect('checkout');
                return;
            }
        }

        parent::indexAction();
    }

    /**
     * Save "Your Details" form data to the quote.
     */
    public function saveAddressAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        $result = ['success' => true];

        try {
            if (!$this->_validateFormKey()) {
                Mage::throwException($this->__('There was an error with form submit.'));
            }

            if ($this->getRequest()->isPost()) {
                $session = Mage::getSingleton('customer/session');
                /**
                 * @var Mage_Customer_Model_Customer
                 */
                $customer = $session->getCustomer();
                $customerData = $this->getRequest()->getParams();

                // Save Customer Data
                $customer->addData([
                    'dob' => $customerData['dob'] ?? '',
                    'firstname' => $customerData['first_name'] ?? '',
                    'lastname' => $customerData['last_name'] ?? '',
                    'secondary_number' => $customerData['secondary_number'] ?? '',
                    'primary_number' => $customerData['primary_number'] ?? '',
                    'south_african_id_number' => $customerData['south_african_id_number'] ?? '',
                    'south_african_document_type' => $customerData['south_african_document_type'] ?? '',
                    'country_of_citizenship' => $customerData['country_of_citizenship'] ?? ''
                ])->save();

                if ($customerData['customer_type'] == 'business') {
                    $this->_saveCustomerAdditionalData($customerData);
                    $this->_saveQuoteBillingAddress($customerData['address']);
                    $this->_saveCustomerResidenceData($customerData['postal']);
                }

                $session->setRegistrationAddress($this->getRequest()->getParam('address', []));
                Mage::dispatchEvent('customer_address_update',
                    [
                        'customer' => $customer,
                        'request_data' => $customerData
                    ]
                );

                $result['delivery_price'] = Mage::helper('rockar_checkout/delivery')->getHomeDeliveryPrice();
                $result['updated_address'] = Mage::helper('rockar_checkout/order')->getAddress();
            }
        } catch (Mage_Core_Exception $e) {
            $result = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        } catch (Exception $e) {
            $result = [
                'error' => true,
                'message' => $this->__('There was an error with form submit.')
            ];

            Mage::logException($e);
        }

        $this->sendJson($result);
    }

    /**
     * Save customer additional data.
     *
     * @param array $postData
     * @return array
     */
    private function _saveCustomerAdditionalData($postData)
    {
        $companyData = [
            'company_name' => $postData['company']['name'] ?? '',
            'company_type' => $postData['company']['type'] ?? '',
            'registration_number' => $postData['company']['registration_number'] ?? '',
            'vat_number' => $postData['company']['vat_number'] ?? '',
            'designation' => $postData['company']['designation'] ?? '',
            'contact_number' => $postData['company']['contact_number'] ?? '',
            'is_company' => true
        ];

        return Mage::getModel('rockar_checkout/quote_additional')->saveCustomerAdditionalData($companyData);
    }

    /**
     * Save quote billing address.
     *
     * @param array $data
     * @return array
     */
    private function _saveQuoteBillingAddress($data)
    {
        $address = Mage::getSingleton('checkout/session')->getQuote()
            ->getBillingAddress()
            ->addData($data)
            ->save();

        return [
            'error' => (bool) $address->getId(),
            'message' => Mage::helper('rockar_checkout')->__('Your residence has been saved.')
        ];
    }

    /**
     * Save customer residential data.
     *
     * @param array $postData
     * @return array
     */
    private function _saveCustomerResidenceData($postData)
    {
        $postalData = [
            'address_1' => $postData['street'][0] ?? '',
            'address_2' => $postData['street'][1] ?? '',
            'region' => $postData['region'] ?? '',
            'city' => $postData['city'] ?? '',
            'postcode' => $postData['postcode'] ?? ''
        ];

        return Mage::getModel('rockar_checkout/quote_additional')->saveCustomerResidenceData($postalData);
    }

    /**
     * Product validation action.
     * Replaces the product in cart if the current one is bought or reserved since checkout process started
     */
    public function validateQuoteItemAction()
    {
        $result = Mage::helper('peppermint_checkout/ReplacementProduct')->expireQuoteProduct();
        $step = $this->getRequest()->getParam('currentStep');
        $quote = $this->getOnepage()->getQuote();
        $response = $result->getResponse();

        if (!$result->getSuccess()) {
            $this->setResponseHttpStatusCode(self::HTTP_CODE_BAD_REQUEST);
            $this->sendJson($response);

            return;
        }

        if ($result->getAllocateProductNotice()) {
            $stepsOrder = array_flip(Mage::helper('rockar_checkout')->getStepCodes());
            $nextStep = $stepsOrder[$step] > $stepsOrder[Peppermint_Checkout_Helper_Data::DELIVERY_STEP_CODE]
                ? Peppermint_Checkout_Helper_Data::DELIVERY_STEP_CODE
                : Peppermint_Checkout_Helper_Data::ADDRESS_STEP_CODE;

            $this->setResponseHttpStatusCode(self::HTTP_CODE_BAD_REQUEST);
            $quote->setSavedStep($nextStep)->save();
        }

        $this->sendJson($response);

        return;
    }

    /**
     * Custom place order action. Set payment method by default to "checkmo"
     * Remove rockar_barclay_epdq method for moment.
     */
    public function orderPlaceAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        Mage::register('order_place', true);

        try {
            $validateItemResult = Mage::helper('peppermint_checkout/ReplacementProduct')
                ->expireQuoteProduct(!$this->reserveAvailableVin());
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            $response = $validateItemResult->getResponse();

            if (!$validateItemResult->getSuccess()) {
                $this->setResponseHttpStatusCode(self::HTTP_CODE_BAD_REQUEST);
                $this->sendJson($response);

                return;
            }

            if ($validateItemResult->getAllocateProductNotice()) {
                $nextStep = Peppermint_Checkout_Helper_Data::DELIVERY_STEP_CODE;
                $this->setResponseHttpStatusCode(self::HTTP_CODE_BAD_REQUEST);
                $quote->setSavedStep($nextStep)->save();
                $this->sendJson($response);

                return;
            }

            if (!$this->_validateFormKey()) {
                Mage::throwException($this->__('Sorry, product data are expired. Please check selected options and try again.'));
            }

            $quote = Mage::getSingleton('checkout/session')->getQuote();
            Mage::dispatchEvent('rockar_sales_order_place_before', ['onepage_controller' => $this]);
            $quote->getPayment()->setMethod('checkmo');
            $quote->collectTotals()->save();

            if ($quote->getShippingAddress()->getShippingMethod() === join('_', [$this->getFlatRateCode(), $this->getFlatRateCode()])) {
                $this->checkTimeSlots();
            }

            $this->_validateFinances($quote);

            $this->_startPlacingOrder();

            try {
                $this->getOnepage()->saveOrder();
            } catch (Exception $e) {
                Mage::logException($e);

                // Set reservation status to error
                $this->_errorPlacingOrder();

                // Set exception code to leave reservation (will be cleaned after 2 mins by cron)
                // and report error
                throw new Exception(
                    $e->getMessage(),
                    self::EXCEPTION_PLACING_ORDER
                );
            }

            $redirectUrl = Mage::getUrl('checkout/onepage/success', ['_secure' => true]);

            if ($customRedirect = $this->getOnepage()->getCheckout()->getRedirectUrl()) {
                $redirectUrl = $customRedirect;
            }

            $this->getOnepage()->getQuote()->save();

            $response = [
                'success' => true,
                'redirect' => $redirectUrl
            ];
        } catch (Exception $e) {
            Mage::logException($e);

            if (
                $e->getCode() !== self::EXCEPTION_ALREADY_PROCESSING
                && $e->getCode() !== self::EXCEPTION_PLACING_ORDER
            ) {
                Mage::helper('peppermint_leadtime')->removeVinReservation(
                    Mage::getSingleton('customer/session')->getCustomer()->getId()
                );
            }

            $this->setResponseHttpStatusCode(self::HTTP_CODE_INTERNAL_SERVER_ERROR);
            $response = [
                'error' => true,
                'message' => $this->__($e->getMessage())
            ];
            $response['slots_taken'] = 0;
            $helper = Mage::helper('rockar_checkout/delivery');

            switch ($e->getCode()) {
                case self::EXCEPTION_CODE_TIMESLOTS_TAKEN:
                    $response['stores_data'] = $helper->getCheckoutStoresFormatted();
                    $response['slots_taken'] = 1;
                    break;
                case self::EXCEPTION_CODE_DELIVERY_DATE:
                    $response['message_title'] = $helper->__('Selected delivery date is no longer available!');
                    break;
                case self::EXCEPTION_OUT_OF_STOCK:
                    $response['message_title'] = $helper->__('Product no longer available');
                    $response['out_of_stock'] = 1;
                    break;
                case self::EXCEPTION_ALREADY_PROCESSING:
                    if ($this->_waitForTheOrder()) {
                        // We have no "placing order" error, order should be available in My Account
                        $response['message_title'] = $helper->__('Order is being created');
                        $response['out_of_stock'] = 1;
                        $response['redirect'] = Mage::getUrl('customer/account', ['_secure' => true]);
                    }
                    break;
            }
        }

        if ($response['success']) {
            Mage::dispatchEvent('peppermint_checkout_controller_success_after', [
                'order_id' => $this->getOnepage()->getCheckout()->getLastOrderId()
            ]);
        }

        $this->sendJson($response);
    }

    /**
     * Put reservation in a sate of placing order to prevent duplicated orders for the same customer
     *
     * @return $this
     */
    protected function _startPlacingOrder()
    {
        if ($this->_reservationRecordId) {
            $reservation = Mage::getModel('peppermint_leadtime/reservation')->load($this->_reservationRecordId);
            $reservation->setPlacingOrder(Peppermint_LeadTime_Helper_Data::PLACING_ORDER_ACTIVE)
                ->save();
        }

        return $this;
    }

    /**
     * Put reservation in a sate of error to show error in parallel session
     *
     * @return $this
     */
    protected function _errorPlacingOrder()
    {
        if ($this->_reservationRecordId) {
            $reservation = Mage::getModel('peppermint_leadtime/reservation')->load($this->_reservationRecordId);
            $reservation->setPlacingOrder(Peppermint_LeadTime_Helper_Data::PLACING_ORDER_ERROR)
                ->save();
        }

        return $this;
    }

    /**
     * If 2 order creation processes are detected for some reason, wait till
     * the process is done before redirection
     *
     * @return bool
     */
    protected function _waitForTheOrder()
    {
        if ($this->_reservationRecordId) {
            $retries = 0;

            do {
                sleep(5);
                $reservation = Mage::getModel('peppermint_leadtime/reservation')->load($this->_reservationRecordId);

                if ($reservation->getPlacingOrder() == Peppermint_LeadTime_Helper_Data::PLACING_ORDER_ERROR) {
                    return false;
                }
            } while ($reservation->getId() && $retries++ < self::RESERVATION_WAIT_RETRIES);

            return $reservation->isEmpty();
        }

        return false;
    }

    /**
     * Save form data to the quote.
     *
     * @return string
     */
    public function saveDepositDetailsAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        $result = [];

        try {
            if (!$this->_validateFormKey()) {
                Mage::throwException($this->__('There was an error with form submit.'));
            }

            if ($this->getRequest()->isPost()) {
                $data = $this->getRequest()->getPost();
                if (empty($data)) {
                    $result = [
                        'error' => true,
                        'message' => $this->__('There was an error with your request.')
                    ];
                } else {
                    $quoteItem = Mage::helper('rockar_checkout')->getQuoteItem();

                    $quoteItem->setData('deposit_source', $data['sourceOfDeposit']);
                    $quoteItem->setData('deposit_other_description', $data['sourceDescription']);

                    $quoteItem->save();

                    $result = [
                        'error' => false,
                        'message' => $this->__('Your details has been saved.')
                    ];
                }
            }
        } catch (Mage_Core_Exception $e) {
            $result = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        } catch (Exception $e) {
            $result = [
                'error' => true,
                'message' => $this->__('There was an error with form submit.')
            ];

            Mage::logException($e);
        }

        $this->sendJson($result);
    }

    /**
     * Save "Credit application" residence form data to the quote.
     *
     * @return string
     */
    public function saveResidenceAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        try {
            if (!$this->_validateFormKey()) {
                Mage::throwException($this->__('There was an error with form submit.'));
            }

            if ($this->getRequest()->isPost()) {
                $data = $this->getRequest()->getPost();
                $result = Mage::getModel('peppermint_checkout/quote_additional')->saveCustomerResidenceData($data);
            }
        } catch (Mage_Core_Exception $e) {
            $result = $this->_formatErrorException($e);
        } catch (Exception $e) {
            $result = $this->_formatErrorException($e, $this->__('There was an error with form submit.'));
        }

        $this->sendJson($result);
    }

    /**
     * Save / load income details.
     *
     * @return void
     */
    public function incomeDetailsAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        /** @var Peppermint_Checkout_Helper_Quote_Additional_Income $helper */
        $helper = Mage::helper('peppermint_checkout/quote_additional_income');

        try {
            if (!$this->_validateFormKey()) {
                Mage::throwException($this->__('There was an error with form submit.'));
            }

            switch ($this->getRequest()->getMethod()) {
                case Zend_Http_Client::POST:
                    $helper->saveData($this->getRequest()->getParams());
                    $response = [
                        'error' => false,
                        'message' => $this->__('Your details have been saved.')
                    ];
                    break;
                case Zend_Http_Client::GET:
                    $data = $helper->getData();
                    $response = [
                        'error' => false,
                        'data' => $data,
                        'message' => $this->__('Your details have been loaded.')
                    ];
                    break;
                default:
                    $response = [
                        'error' => true,
                        'message' => $this->__('Method not implemented!')
                    ];
                    break;
            }
        } catch (Mage_Core_Exception $e) {
            $this->_formatErrorException($e);
        } catch (Exception $e) {
            $this->_formatErrorException($e, $this->__('An exception occured in your request!'));
        }

        if ($response['error']) {
            $this->setResponseHttpStatusCode(static::HTTP_CODE_BAD_REQUEST);
        }
        $this->sendJson($response);
    }

    /**
     * Formating error exception.
     *
     * @param Exception $e
     * @param string $message
     * @return []
     */
    private function _formatErrorException($e, $message = null)
    {
        Mage::logException($e);

        return [
            'error' => true,
            'message' => $message ?: $e->getMessage()
        ];
    }

    /**
     * Get active customer billing address as JSON.
     *
     * @return void
     */
    public function getAddressAction()
    {
        if (!$this->checkFormKey()) {
            return;
        }

        $primaryAddress = [
            'address_1' => '',
            'address_2' => '',
            'city' => '',
            'region' => '',
            'postcode' => ''
        ];

        $address = Mage::getSingleton('customer/session')->getCustomer()
            ->getPrimaryBillingAddress();

        if ($address) {
            $street = $address->getStreet();
            $primaryAddress['address_1'] = $street[0] ?? '';
            $primaryAddress['address_2'] = $street[1] ?? '';
            $primaryAddress['city'] = $address->getCity();
            $primaryAddress['region'] = $address->getRegion();
            $primaryAddress['postcode'] = $address->getPostcode();
        }

        $this->sendJson($primaryAddress);
    }

    /**
     * Check time-slots prior to Place order
     *
     * @throws Exception
     */
    public function checkLeadTimeAmount()
    {
        $isProductAvailable = Mage::helper('peppermint_checkout/ReplacementProduct')->checkProductLeadTime();

        if (!$isProductAvailable) {
            throw new Exception(
                $this->__('The Vehicle You Have Selected Is No Longer Available | Please Select Another Vehicle'),
                self::EXCEPTION_OUT_OF_STOCK
            );
        }
    }

    /**
     * Reserves the vin if available. Return false if not
     *
     * @return bool
     * @throws Exception
     */
    public function reserveAvailableVin()
    {
        $quoteHelper = Mage::helper('rockar_checkout/quote');
        $product = $quoteHelper->getFirstSimpleProduct(
            Mage::getSingleton('checkout/session')->getQuote()
        );

        if ($product) {
            $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
            $reservation = Mage::getModel('peppermint_leadtime/reservation')
                ->load($product->getData('sku'), 'vin_number');

            if ($reservation->isEmpty()) {
                $reservation->addData([
                    'vin_number' => $product->getData('sku'),
                    'customer_id' => $customerId,
                    'created_at' => Varien_Date::now()
                ]);

                $reservation->save();

                $this->_reservationRecordId = $reservation->getId();

                return true;
            } elseif ($reservation->getCustomerId() == $customerId) {
                $this->_reservationRecordId = $reservation->getId();

                if ($reservation->getPlacingOrder() == Peppermint_LeadTime_Helper_Data::PLACING_ORDER_ACTIVE) {
                    throw new Exception(
                        $this->__(
                            'Your order is being processed right now, please check your account for the results.'
                        ),
                        self::EXCEPTION_ALREADY_PROCESSING
                    );
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Validate if finance fields are populated in quote item
     *
     * @param Mage_Sales_Model_Quote $quote
     * @throws Exception
     */
    private function _validateFinances($quote)
    {
        $quoteItem = Mage::helper('rockar_checkout/quote')->getFirstQuoteItem($quote);

        if (
            empty($quoteItem->getData('finance_data'))
            || empty($quoteItem->getData('finance_data_variables'))
            || empty($quoteItem->getData('finance_overlay'))
        ) {
            throw new Exception($this->__('Finance data is missing. Please, try again later.'));
        }
    }

    /**
     * Rewrite of parent function to add form key validation
     *
     * {@inheritDoc}
     */
    public function saveEmploymentAction()
    {
        if (!$this->checkFormKey()) {
            return;
        }

        parent::saveEmploymentAction();
    }

    /**
     * Rewrite of parent function to add form key validation
     *
     * {@inheritDoc}
     */
    public function saveDetailsAction()
    {
        if (!$this->checkFormKey()) {
            return;
        }

        parent::saveDetailsAction();
    }

    /**
     * Check if form key matches the one generated in session
     * and show error if it does not.
     *
     * @return bool
     */
    protected function checkFormKey()
    {
        if (!$this->_validateFormKey()) {
            $result = [
                'error' => true,
                'message' => $this->__('There was an error with form submit.')
            ];

            $this->sendJson($result);

            return false;
        }

        return true;
    }

    /**
     * Rewrite of parent function to add _tidyQuote call
     *
     * {@inheritDoc}
     */
    public function preDispatch()
    {
        parent::preDispatch();

        $this->_tidyQuote();

        return $this;
    }

    /**
     * PEP-3572
     *
     * Addressing issue when there are more than one vehicles added to the quote
     */
    protected function _tidyQuote()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $saveQuote = false;
        $allItems = $quote->getAllItems();

        // If quote has an unusual item count, remove all items from quote except the first visible config/simple pair
        if (count($allItems) > 2) {
            $saveQuote = true;
            $itemIds = [];
            $helper = Mage::helper('rockar_checkout/quote');

            $itemIds[] = $helper->getFirstVisibleQuoteItem($quote)->getItemId();
            $itemIds[] = $helper->getFirstVisibleQuoteItem(
                $quote,
                Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
            )->getItemId();

            foreach ($allItems as $item) {
                if (!in_array($item->getId(), $itemIds)) {
                    $item->delete();
                }
            }
        }

        // Set quote item product qty to 1, if it's not 1
        foreach ($allItems as $item) {
            if ((int) $item->getQty() !== 1) {
                $item->setQty(1);
                $saveQuote = true;
            }
        }

        if ($saveQuote) {
            $quote->save();
        }
    }
}

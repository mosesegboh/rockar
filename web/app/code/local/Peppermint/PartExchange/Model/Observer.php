<?php
/**
 * @category  Peppermint
 * @package   Peppermint_PartExchange
 * @author    Lucian Mesaros <lucian.mesaros@rockar.com>, Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_PartExchange_Model_Observer extends Rockar2_PartExchange_Model_Observer
{
    /**
     * Modify Column Header.
     *
     * @param Varien_Event_Observer $observer
     * @return Peppermint_PartExchange_Model_Observer
     */
    public function setCustomHeader(Varien_Event_Observer $observer)
    {
        $observer->getData('block')
            ->getColumn('part_exchange_value')
            ->setHeader(Mage::helper('rockar_partexchange')->__('Trade-in Value'));

        return $this;
    }

    /**
     * Save part exchange values for order.
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function populateOrderPartExchange(Varien_Event_Observer $observer)
    {
        $orderItem = Mage::helper('rockar_checkout/order')->getFirstVisibleItem($observer->getOrder());

        if ($orderItem) {
            $partExchange = null;

            // If PX is deleted from the session but this doesn't pass TRUE, then Magento gets PX from the collection,
            // and the PX is carried over to amended order even if it was deleted during the amendment
            if (Mage::getSingleton('adminhtml/session_quote')->getPartExchange(true)) {
                /**
                 * PX in session from reorder
                 */
                $partExchange = Mage::getSingleton('adminhtml/session_quote')->getPartExchange();

                $additionalInfo = [];
                $getCheckboxesData = $partExchange->getData('checkboxes');

                if (is_array($getCheckboxesData)) {
                    foreach ($getCheckboxesData as $checkbox) {
                        if (filter_var($checkbox['checked'], FILTER_VALIDATE_BOOLEAN)) {
                            $additionalInfo[] = (int) $checkbox['id'];
                        }
                    }
                }

                $partExchange->setCheckboxes(
                    Mage::helper('rockar_all')->jsonEncode($additionalInfo)
                );

                if ($manualData = $partExchange->getData('manual_data')) {
                    $partExchange->setCarModel($manualData['model']);
                    $partExchange->setManualData(Mage::helper('rockar_all')->jsonEncode($manualData));
                }
            } else if ($order = $orderItem->getPartExchangeId()) {
                /**
                 * Regular checkout with PX model on quote.
                 */
                $partExchange = Mage::getModel('rockar_partexchange/partExchange')->load($order);
            }

            if ($partExchange !== null) {
                Mage::getModel('rockar_partexchange/order')->addData(
                    [
                        'order_id' => $orderItem->getOrderId(),
                        'cap_id' => $partExchange->getCapId(),
                        'car_model' => $partExchange->getCarModel(),
                        'car_year' => $partExchange->getCarYear(),
                        'license_plate' => $partExchange->getLicensePlate(),
                        'car_mileage' => $partExchange->getCarMileage(),
                        'part_exchange_value' => $partExchange->getPartExchangeValue(),
                        'car_condition' => $partExchange->getCarCondition(),
                        'checkboxes' => $partExchange->getCheckboxes(),
                        'outstanding_finance' => $partExchange->getOutstandingFinance(),
                        'outstanding_finance_settlement' => Mage::helper('rockar_partexchange')
                            ->loadPartExchangeFromSession(Rockar_PartExchange_Helper_Data::PART_EXCHANGE_SESSION_KEY)
                            ->getData('outstanding_finance_settlement'),
                        'px_id' => $partExchange->getId(),
                        'manual_data' => $partExchange->getManualData(),
                        'bidding_id' => $partExchange->getBiddingId(),
                        'first_date_of_registration' => $partExchange->getFirstDateOfRegistration(),
                        'make_name' => $partExchange->getMakeName(),
                        'make_code' => $partExchange->getMakeCode(),
                        'original_valuation' => $partExchange->getOriginalValuation(),
                        'vin' => $partExchange->getVin()
                    ]
                )->save();
            }
        }

        return $this;
    }

    /**
     * Upon login checks if user has part exchange and
     * sets it into the session and saves it into account.
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function setDataObject(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('rockar_partexchange');
        $pxKeys = [
            Rockar_PartExchange_Helper_Data::PART_EXCHANGE_SESSION_KEY,
            Rockar_PartExchange_Helper_Data::RUNNING_COSTS_PART_EXCHANGE_KEY,
            Rockar_PartExchange_Helper_Data::CUSTOMER_PART_EXCHANGE_SESSION_KEY
        ];

        $customerId = $observer->getCustomer()
            ->getId();

        $customerPx = Mage::getModel('rockar_partexchange/partExchange')->getCollection()
            ->addFieldToFilter('customer_id', ['eq' => $customerId])
            ->setPageSize(1)
            ->setCurPage(1)
            ->getFirstItem();

        $sessionPartExchange = $helper->loadPartExchangeFromSession(Rockar_PartExchange_Helper_Data::CUSTOMER_PART_EXCHANGE_SESSION_KEY);
        $currentGuestSession = $sessionPartExchange;

        if ($customerPx->getId() && !$sessionPartExchange->getVrm()) {
            $helper->loadPartExchangeIntoSession($customerId, null, $pxKeys);
            $sessionPartExchange = $helper->loadPartExchangeFromSession(Rockar_PartExchange_Helper_Data::CUSTOMER_PART_EXCHANGE_SESSION_KEY);
        }

        if (!empty($sessionPartExchange->getData())) {
            if (!empty($currentGuestSession->getVrm())) {
                Mage::getSingleton('core/session')->addSuccess(Mage::helper('core')->__('New Trade-in has been successfully added to your account'));
            }
            $helper->removeCustomerPartExchange();
            $pxId = $helper->saveCustomerPartExchange($sessionPartExchange);
            $helper->updateCheckout(['px_id' => $pxId]);
            $helper->loadPartExchangeIntoSession($customerId, $pxId, $pxKeys);
        }

        return $this;
    }

    /**
     * Sanitize Part Exchange checkboxes
     *
     * @param Varien_Event_Observer $observer
     */
    public function sanitizePartexchangeCheckboxes(Varien_Event_Observer $observer)
    {
        $checkboxes = $observer->getEvent()->getObject();
        Mage::helper('peppermint_all')->sanitizeData($checkboxes, ['title', 'tooltip', 'error_message'], true);
    }

    /**
     * Sanitize order comment
     *
     * @param Varien_Event_Observer $observer
     */
    public function sanitizePartexchangeSlider(Varien_Event_Observer $observer)
    {
        $slider = $observer->getEvent()->getObject();
        Mage::helper('peppermint_all')->sanitizeData($slider, ['title', 'tooltip', 'description'], true);
    }
}

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Orderamend
 * @author    Juris Krislauks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Orderamend_Model_Observer extends Rockar_Orderamend_Model_Observer
{
    /**
     * After adding lead time to simple product - need to add it also to configurable since
     * BE orderView uses configurable product's lead time
     * Runs after item is added to quote.
     *
     * Event: rockar_lead_time_admin_quote_add_lead_time_after
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function updateConfigurableProductLeadTime(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();

        $quoteItem = $event->getItem();
        $leadTime = $event->getLeadTime()->getData();

        if ($parentItem = $quoteItem->getParentItem()) {
            $parentItem->setData('lead_time', (Mage::helper('rockar_all')->jsonEncode($leadTime)))
                ->save();
        }
    }

    /**
     * Convert order item to quote item
     * If item has parent item then update it instead since Magento passes only one orderItem in case multiple are created.
     *
     * Event: sales_convert_order_item_to_quote_item
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function convertOrderItemToQuoteItem(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();

        $quoteItem = $event->getQuoteItem();
        $orderItem = $event->getOrderItem();
        /** @var Rockar_Checkout_Helper_Convert $convertHelper */
        $convertHelper = Mage::helper('rockar_checkout/convert');

        $parentItem = $quoteItem->getParentItem();

        if ($parentItem && $parentItem->getProductType() == $orderItem->getProductType()) {
            foreach ($convertHelper->getOrderItemToQuoteItemAttributes() as $quoteItemField => $orderItemField) {
                $parentItem->setData($quoteItemField, $orderItem->getData($orderItemField));
            }
            // Update simple since OA view uses simple product leadTime
            $quoteItem->setLeadTime($parentItem->getLeadTime());
        } elseif ($quoteItem->getProductType() == $orderItem->getProductType()) {
            foreach ($convertHelper->getOrderItemToQuoteItemAttributes() as $quoteItemField => $orderItemField) {
                $quoteItem->setData($quoteItemField, $orderItem->getData($orderItemField));
            }
        }
    }

    /**
     * Update Part Exchange data
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function updatePartExchange(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        $messages = $event->getData('messages');
        $partExchangeData = $event->getData('partExchangeData');
        $partExchangeHelper = Mage::helper('rockar_orderamend/partExchange_data');
        $sessionQuote = Mage::getSingleton('adminhtml/session_quote');
        $helper = Mage::helper('rockar_orderamend');

        if (isset($partExchangeData['delete']) && $partExchangeData['delete']) {
            $sessionQuote->setPriceOverrideValueByKey('part_exchange', 0.0);
            $sessionQuote->deletePartExchange();
            $helper->getFirstVisibleQuoteItem()
                ->setPartExchangeId(null);
            $helper->getFirstQuoteItem()
                ->setPartExchangeId(null);
            $messages->setData('success', ['Trade-in is removed.']);

            return;
        }

        $outstandingFinance = $partExchangeData['outstanding_finance'] ?? false;
        $outstandingFinanceSettlementMethod = $partExchangeData['outstanding_finance_settlement'] ?? false;
        $manualPartExchangeValue = $partExchangeData['manual_value'] ?? false;
        $manualData = $partExchangeData['manual_data'] ?? null;
        $partExchange = $sessionQuote->getPartExchange(true);
        $checkboxes = $partExchangeData['additionalInfo'] ?? null;
        $biddingID = $partExchangeData['bidding_id'] ?? null;

        // create new partexchange if it does not exist and is manual
        if (!$partExchange
            && $partExchangeHelper->getPartExchangeUseManualForm()
            && !empty($partExchangeData['apiless'])
        ) {
            $partExchange = new Varien_Object();
        }

        if ($partExchange) {
            $mData = !empty($manualData) ? array_filter($manualData) : null;

            if ($partExchangeHelper->getPartExchangeUseManualForm()
                && !empty($partExchangeData['apiless'])
                && !empty($mData)
            ) {
                $data = [
                    'license_plate' => $partExchangeData['vrm'] ?? '',
                    'car_condition' => $partExchangeData['carCondition'] ?? '',
                    'car_mileage' => isset($partExchangeData['mileage'])
                        ? Mage::helper('rockar_partexchange')->calculateMileage($partExchangeData['mileage'])
                        : 0,
                    'product_name' => $partExchangeData['product_name'] ?? '',
                    'car_model' => $manualData['model'] ?? '',
                    'car_year' => $partExchangeData['plate_year'] ?? '',
                ];

                if ($checkboxes !== null) {
                    $data['checkboxes'] = $partExchangeHelper->formatCheckboxes($checkboxes);
                }

                $partExchange->setData($data);
                $partExchange->setData('manual_data', $manualData);
            }

            if ($outstandingFinance !== false) {
                $partExchange->setData('outstanding_finance', $outstandingFinance);
            }

            if ($outstandingFinanceSettlementMethod !== false) {
                $partExchange->setData('outstanding_finance_settlement', $outstandingFinanceSettlementMethod);
            }

            if ($biddingID) {
                $partExchange->setData('bidding_id', $biddingID);
            }

            if ($partExchangeHelper->getPartExchangeAllowManualPrice()
                || $partExchangeHelper->getPartExchangeUseManualForm()
            ) {
                if ($manualPartExchangeValue !== false) {
                    //todo: remove 'part_exchange_value' setData; change updatePxFinalPrice location regarding logic.
                    $partExchange->setData('part_exchange_value', $manualPartExchangeValue);
                }
            }

            $sessionQuote->updatePartExchange($partExchange->getData());
            Mage::helper('rockar_orderamend/priceOverride')
                ->updatePxFinalPrice($partExchange->getData('part_exchange_value'));
        }
    }

    /**
     * Re-evaluate finance when product is added to quote
     *
     * @param Varien_Event_Observer $observer
     * @throws Mage_Core_Exception
     * @return void
     */
    public function updatePartExchangeAndFinanceData(Varien_Event_Observer $observer)
    {
        parent::updatePartExchangeAndFinanceData($observer);

        $quote = Mage::helper('rockar_orderamend')->getQuote();

        // Updating quote during OA proccess product title doesn't updates
        if (!$quote->getProductTitle()) {
            $quote->setProductTitle(
                Mage::helper('rockar_checkout/quote')->getFirstQuoteItem($quote)->getName()
            )->save();
        }
    }

    /**
     * Validate if order products exists
     *
     * Event: rockar_orderamend_create_before
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function validateOrderProduct(Varien_Event_Observer $observer)
    {
        $orderId = Mage::app()->getRequest()->getParam('order_id', false);
        $resource = Mage::getSingleton('core/resource');
        $readAdapter = $resource->getConnection('core_read');

        $select = $readAdapter->select()
            ->from(['main_table' => $resource->getTableName('sales/order_item')], ['product_id'])
            ->joinInner(['product_entity' => $resource->getTableName('catalog/product')],
                'main_table.product_id = product_entity.entity_id',
                []
            )
            ->where('main_table.order_id = ?', $orderId)
            ->where('main_table.product_type = ?', Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
            ->limit(1);

        // If simple doesn't exist in the catalog, try to update order Item product data
        if ($readAdapter->fetchOne($select) === false) {
            try {
                Mage::helper('peppermint_sales/order')->refreshOrderProductData(Mage::getModel('sales/order')->load($orderId));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
               // stop redirect to order amend page
                $this->_handleOrderProductUpdateFail($orderId);
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($this->__('There is a problem refreshing order product data. Please try again.'));
                // stop redirect to order amend page
                $this->_handleOrderProductUpdateFail($orderId);
            }
        }
    }

    /**
     * Carry over Credit Application ID and status on child order
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function saveCreditAppToChildOrder(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        $newOrder = $event->getData('new_order');
        $oldOrder = $event->getData('old_order');

        $creditAppId = $oldOrder->getData('credit_app_id');
        $status = $oldOrder->getData('credit_app_status');

        if ($creditAppId && $status) {
            try {
                $newOrder->setData('credit_app_id', $creditAppId);
                $newOrder->setData('credit_app_status', $status);
                $newOrder->save();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }

    /**
     * Carry over Dealer ID on child order
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function saveDealerIdToChildOrder(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        $newOrder = $event->getData('new_order');
        $oldOrder = $event->getData('old_order');

        $dealerId = $oldOrder->getData('dealer_id');

        if ($dealerId) {
            try {
                $newOrder->setData('dealer_id', $dealerId);
                $newOrder->save();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }

    /**
     * Retrieve session model
     *
     * @return Mage_Adminhtml_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }

    /**
     * Handle order item product update failure
     *
     * @param int|false $orderId
     * @return Zend_Controller_Response_Http
     */
    protected function _handleOrderProductUpdateFail($orderId)
    {
        return Mage::app()->getResponse()
            ->setRedirect(
                Mage::getModel('adminhtml/url')->getUrl('adminhtml/sales_order/view', ['order_id' => $orderId])
            )
            ->sendResponse();
    }

    /**
     * Update additional data when quote main update has been performed (adding/updating product)
     *
     * @param Varien_Event_Observer $observer
     */
    public function updateQuoteData(Varien_Event_Observer $observer)
    {
        /** @var Mage_Adminhtml_Model_Sales_Order_Create $orderCreateModel */
        $orderCreateModel = $observer->getEvent()
            ->getOrderCreateModel();

        $orderCreateModel->getQuote()
            ->setProductTitle(
                Mage::helper('rockar_checkout/quote')->getFirstVisibleQuoteItem($orderCreateModel->getQuote())->getName()
            );
    }
}

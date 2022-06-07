<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Lucaci Stefan <lucacistefan.alexandru@rockar.com>, Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Sales_Model_Observer
{
    /**
     * Snapshot column name
     */
    const PRICING_DETAILS_SNAPSHOT = 'pricing_details_snapshot';
    const PUBLISHED_TO_DS_SNAPSHOT = 'published_to_ds_date';

    /**
     * Array with attributes to show on order page
     *
     * @var array $_attributesToSelect
     */
    protected $_attributesToSelect = [
        'Fuel Type' => 'fuel_type',
        'Transmission' => 'transmission'
    ];

    /**
     * Core function rewrite to also check if there $adminSession->getUser()
     * before calling ->getUsername() on it
     * {@inheritDoc}
     */
    public function addAdminUserNameToHistory(Varien_Event_Observer $observer)
    {
        if (Mage::app()->getStore()->isAdmin()) {
            $adminSession = Mage::getSingleton('admin/session');
            $history = $observer->getStatusHistory();

            if ($history->isObjectNew() && $adminSession->getUser()) {
                $history->setUsername($adminSession->getUser()->getUsername());
            }
        }
    }

    /**
     * When order is placed, generate OTP document and send mail to customer.
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function onOrderPlacedOtp(Varien_Event_Observer $observer)
    {
        $storeId = Mage::app()->getStore()->getStoreId();
        $helper = Mage::helper('peppermint_sales/otpMail');

        if ($helper->getSalesConfig('enabled', $storeId)) {
            $order = $observer->getEvent()->getOrder();
            // check if order have trade in, if not send mail trigger
            if (!Mage::getModel('rockar_partexchange/order')->load($order->getId(), 'order_id')->getId()) {
                $customerData = Mage::getModel('customer/customer')->load($order->getCustomerId());
                $helper->sendOtpMail($order, $customerData, $storeId);
            }
        }
    }

    /**
     * Push order details to DS after checkout.
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function pushOrderDetailsToDs(Varien_Event_Observer $observer)
    {
        $orderId = $observer->getOrderId();
        $order = $orderId ? Mage::getModel('sales/order')->load($orderId) : null;

        if ($order && $order->getId()) {
            Mage::helper('peppermint_sales/order')->prepareAndPushOrderToDs($order);
        }

        return $this;
    }

    /**
     * Send invoice order details to DS when preparing invoice.
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function sendInvoiceOrderDetailsToDsOnPrepareInvoice(Varien_Event_Observer $observer)
    {
        Mage::helper('peppermint_sales/order')->prepareAndPushOrderToDs($observer->getOrder());

        return $this;
    }

    /**
     * Push order details to DS when refund on invoice is triggered.
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function pushRefundOrderDetailsToDs(Varien_Event_Observer $observer)
    {
        $creditMemo = $observer->getCreditmemo();

        $order = $creditMemo->getOrder();

        if (
            $creditMemo->getState() === Mage_Sales_Model_Order_Creditmemo::STATE_REFUNDED
            && $order->getState() === $order::STATE_CLOSED
        ) {
            Mage::helper('peppermint_sales/order')->prepareAndPushOrderToDs($order);
        }

        return $this;
    }

    /**
     * Push order details to DS when amendment is approved or rejected.
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function pushOrderAmendmentDetailsToDs(Varien_Event_Observer $observer)
    {
        /** @var Peppermint_Sales_Model_Order $amendmentOrder */
        $amendmentOrder = $observer->getAmendmentOrder();
        /** @var Peppermint_Sales_Model_Order $originalOrder */
        $originalOrder = $observer->getOriginalOrder();

        // dont send message to SAP if order of rejected amendment was not invoiced before
        if (
            $amendmentOrder->getStatus() === Peppermint_Sales_Model_Order::STATUS_AMENDMENT_CANCELLED
            && $originalOrder->getStatus() !== Peppermint_Sales_Model_Order::STATUS_INVOICE_SUBMITTED
        ) {
            return $this;
        }

        Mage::helper('peppermint_sales/order')->prepareAndPushOrderToDs($amendmentOrder);

        return $this;
    }

    /**
     * Push order details to DS when order is cancelled.
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function pushOrderCanceledDetailsToDs(Varien_Event_Observer $observer)
    {
        if (!Mage::registry('no_cancel_email')) {
            $event = $observer->getEvent();
            $order = $event->getOrder();

            // dont send message to SAP if order of rejected amendment was not invoiced before
            if (
                $order->getStatus() === Peppermint_Sales_Model_Order::STATUS_AMENDMENT_CANCELLED
                && $order->getHoldBeforeStatus() !== Peppermint_Sales_Model_Order::STATUS_INVOICE_SUBMITTED
            ) {
                return $this;
            }

            Mage::helper('peppermint_sales/order')->prepareAndPushOrderToDs($order, $event->getName());
        }

        return $this;
    }

    /**
     * Push order details to DS when invoice and shipment is submitted.
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function pushOrderPreretailDetailsToDs(Varien_Event_Observer $observer)
    {
        Mage::helper('peppermint_sales/order')->prepareAndPushOrderToDs($observer->getOrder());

        return $this;
    }

    /**
     * When order is accepted from My Account, generate OTP document and send mail to customer.
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function onMyAccountOrderAcceptedOtp(Varien_Event_Observer $observer)
    {
        $storeId = Mage::app()->getStore()
            ->getStoreId();
        $helper = Mage::helper('peppermint_sales/otpMail');

        if ($helper->getSalesConfig('enabled', $storeId)) {
            $order = $observer->getAmendmentOrder();
            $customerData = Mage::getModel('customer/customer')->load($order->getCustomerId());
            $helper->sendOtpMail($order, $customerData, $storeId);
        }
    }

    /**
     * Push order details to DS when tracking: vehicle arrived is triggered.
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function pushTrackingOrderArrivedDetailsToDs(Varien_Event_Observer $observer)
    {
        Mage::helper('peppermint_sales/order')->prepareAndPushOrderToDs($observer->getOrder());

        return $this;
    }

    /**
     * Push order details to DS when tracking: vehicle in transit is triggered.
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function pushTrackingOrderInTransitDetailsToDs(Varien_Event_Observer $observer)
    {
        Mage::helper('peppermint_sales/order')->prepareAndPushOrderToDs($observer->getOrder());

        return $this;
    }

    /**
     * Stores Vin number to order
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function addVinNumberToOrder(Varien_Event_Observer $observer)
    {
        $order = $observer->getOrder();
        $product = Mage::helper('rockar_checkout/order')->getFirstSimpleProduct($order);

        if ($product && $product->getId()) {
            $vinNumber = $product->getVinNumber() ?: Mage::getResourceModel('catalog/product')->getAttributeRawValue(
                $product->getId(),
                'vin_number',
                Mage::app()->getStore()->getId()
            );
            $order->setVinNumber($vinNumber);
        }
    }

    /**
     * Sales order grid table prepareColumns
     *
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    public function salesOrderGridPrepareColumns(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        // Adding Vin Number to Order Grid
        $block->addColumnAfter(
            'vin_number',
            [
                'header' => $block->helper('sales')->__('VIN Number'),
                'index' => 'vin_number',
                'filter_index' => 'vin_number'
            ],
            'product_title'
        );

        // Add Credit Application ID column to Order Grid
        $block->addColumnAfter(
            'credit_app_id',
            [
                'header' => $block->helper('sales')->__('Credit Application ID'),
                'index' => 'credit_app_id',
                'filter_index' => 'credit_app_id'
            ],
            'finance_payment_title'
        );

        $block->sortColumnsByOrder();

        return $block;
    }

    /**
     * Sanitize order comment
     *
     * @param Varien_Event_Observer $observer
     */
    public function sanitizeOrderStatus(Varien_Event_Observer $observer)
    {
        $statusHistory = $observer->getEvent()->getStatusHistory();
        Mage::helper('peppermint_all')->sanitizeData($statusHistory, 'comment');
    }

    /**
     * Stores pricing details snapshot to order item table
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function setItemPrices(Varien_Event_Observer $observer)
    {
        $this->createSnapshot($observer, $pricingDetails = true);
    }

    /**
     * Stores ‘published_to_ds_date' variable snapshot to order item table
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function setPublishedToDsDate(Varien_Event_Observer $observer)
    {
        $this->createSnapshot($observer);
    }

    /**
     * Stores snapshot to sales item table
     *
     * @param $observer
     * @param $pricingDetails
     * @return void
     */
    public function createSnapshot($observer, $pricingDetails = null)
    {
        $amendHelper = Mage::helper('peppermint_orderamend/order');
        $checkoutHelper = Mage::helper('rockar_checkout/order');
        $allHelper = Mage::helper('rockar_all');
        $orderHelper = Mage::helper('peppermint_sales/order');
        $productModel = Mage::getModel('catalog/product');
        $orderModel = Mage::getModel('sales/order');
        $orderId = $observer->getOrder()
            ->getId();

        /**
         * @var Mage_Sales_Model_Order $order
         */
        $order = $observer->getOrder();
        $oldOrder = $amendHelper->getPreviousOrder($order);
        $newSnapshot = true;

        /** Don't create new snapshot if not the first time placing order
         *  and the product is still the same
         */
        if ($oldOrder && $amendHelper->isProductSame($order)) {
            $newSnapshot = false;
        }

        foreach ($order->getAllItems() as $item) {
            $snapshotData = $pricingDetails ? [] : '';

            if ($newSnapshot) {
                // create new snapshot
                $product = $pricingDetails
                    ? $productModel->load($item->getProduct()->getId())
                    : $checkoutHelper->getFirstSimpleProduct($orderModel->load($orderId));

                $snapshotData = $pricingDetails
                    ? $allHelper->jsonEncode(
                        [
                            'co2_tax' => $product->getCo2Tax(),
                            'mplan_amount' => $product->getMplanPrice(),
                            'base_price' => $product->getBasePrice(),
                            'options_only_price' => $product->getOptionsOnlyPrice(),
                            'total_price' => $product->getTotalPrice(),
                            'excl_mplan_amount' => $product->getTotalPriceExclMplan(),
                            'price' => $product->getPrice(),
                            'options' => $orderHelper->prepareOptionsData($product)
                        ]
                    )
                    : $product->getPublishedToDsDate();
            } else {
                // transfer pricing snapshot from previous order
                $oldItem = null;

                if ($item->getProductType() === Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
                    $oldItem = $checkoutHelper->getFirstSimpleOrderItem($oldOrder);
                } else if ($item->getProductType() === Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                    $oldItem = $checkoutHelper->getFirstVisibleItem($oldOrder);
                }

                if ($oldItem) {
                    $snapshotData = $pricingDetails
                        ? $oldItem->getPricingDetailsSnapshot()
                        : $oldItem->getPublishedToDsDate();
                }
            }

            $item->setData(
                $pricingDetails
                    ? self::PRICING_DETAILS_SNAPSHOT
                    : self::PUBLISHED_TO_DS_SNAPSHOT,
                $snapshotData
            );
        }
    }

    /**
     * Set flag to unlock order amend for particular vin
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function unlockOrderAmend(Varien_Event_Observer $observer)
    {
        if (Mage::helper('peppermint_orderamend')->getCanUnlockAmendEnabled()) {
            $helper = Mage::helper('peppermint_sales/order');

            $helper->setLockOrUnlockOrderAmend(
                [$helper->getOrderVinNumber($observer->getOrder())],
                Peppermint_Sales_Model_AdditionalData::CAN_AMEND_UNLOCK
            );
        }
    }

    /**
     * Set flag to lock order amend for particular vin
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function lockOrderAmend(Varien_Event_Observer $observer)
    {
        if (Mage::helper('peppermint_orderamend')->getCanUnlockAmendEnabled()) {
            $helper = Mage::helper('peppermint_sales/order');

            $helper->setLockOrUnlockOrderAmend(
                [$helper->getOrderVinNumber($observer->getOrder())],
                Peppermint_Sales_Model_AdditionalData::CAN_AMEND_LOCK
            );
        }
    }

    /**
     * Set flag to lock order amend button after amend rejection
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function lockOrderAmendAfterAmendReject(Varien_Event_Observer $observer)
    {
        /** @var Peppermint_Sales_Model_Order $amendmentOrder */
        $amendmentOrder = $observer->getAmendmentOrder();
        /** @var Peppermint_Sales_Model_Order $originalOrder */
        $originalOrder = $observer->getOriginalOrder();

        // Only lock order amend if the original order has been pre-invoice
        if (
            Mage::helper('peppermint_orderamend')->getCanUnlockAmendEnabled()
            && $originalOrder->getStatus() === Peppermint_Sales_Model_Order::STATUS_INVOICE_SUBMITTED
            && $amendmentOrder->getStatus() === Peppermint_Sales_Model_Order::STATUS_AMENDMENT_CANCELLED
        ) {
            $helper = Mage::helper('peppermint_sales/order');
            $helper->setLockOrUnlockOrderAmend(
                [$helper->getOrderVinNumber($originalOrder)],
                Peppermint_Sales_Model_AdditionalData::CAN_AMEND_LOCK
            );
        }
    }

    /**
     * Add additional attributes to the quote in serialized format
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function salesQuoteItemSetAdditionalAttributes(Varien_Event_Observer $observer)
    {
        $quoteItem = $observer->getQuoteItem();
        $product = $observer->getProduct();
        $storeId = $quoteItem->getQuote()->getStoreId();
        $additionalAttributes = [];

        foreach ($this->_attributesToSelect as $attributeLabel => $attributeCode) {
            $additionalAttributes[] = [
                'label' => $attributeLabel,
                'value' => $this->_getAttributeValue($product, $storeId, $attributeCode),
            ];
        }

        if (
            $this->_getAttributeValue($product, false, 'vehicle_condition') !==
            Peppermint_Catalog_Helper_Vehicle::CONDITION_NEW
        ) {
            $additionalAttributes[] = [
                'label' => 'Mileage',
                'value' => number_format(
                        $this->_getAttributeValue($product, $storeId, 'km'), 0, '.', ' '
                    ) . ' km',
            ];
        }

        $quoteItem->setAdditionalAttributes(serialize($additionalAttributes));
    }

    /**
     * Function to get attribute value based on store id
     *
     * @param Mage_Catalog_Model_Product $product
     * @param int|false $storeId
     * @param string $attribute
     * @return mixed
     */
    protected function _getAttributeValue($product, $storeId, $attribute)
    {
        if (!$product) {
            return '';
        }

        return Mage::getResourceSingleton('catalog/product')->getAttribute($attribute)
            ->setStoreId($storeId ?: Mage_Core_Model_App::ADMIN_STORE_ID)
            ->getFrontend()
            ->getValue($product);
    }
}

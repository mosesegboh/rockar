<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Orderstatus
 * @author    Mihai Chezan <mihai.chezan@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Orderstatus_Model_Observer extends Rockar_Orderstatus_Model_Observer
{
    /**
     * Sync admin order status with customer facing status.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function updateCustomerAndAdminOrderStatus(Varien_Event_Observer $observer)
    {
        /** @var Peppermint_Sales_Model_Order $order */
        $statusHelper = Mage::helper('peppermint_orderstatus')->orderStatusMappingUpdate(
            $observer->getOrder() ?: $observer->getAmendmentOrder()
        );

        return $this;
    }

    /**
     * update status invoice submitted.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function updateOrderStatusInvoiceSubmitted(Varien_Event_Observer $observer)
    {
        /** @var Peppermint_Sales_Model_Order $order */
        $order = $observer->getEvent()
            ->getOrder();
        $order->setState($order::STATE_PROCESSING, $order::STATUS_INVOICE_SUBMITTED)
            ->save();

        return $this;
    }

    /**
     * After checkout is completed, moves the order to processing state when no trade in found.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function adjustOrderState(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getOrder();
        $hasPartExchange = Mage::getModel('rockar_partexchange/order')->getCollection()
            ->addFieldToFilter('order_id', $order->getId())
            ->setCurPage(1)
            ->setPageSize(1)
            ->getSize();

        if (!$hasPartExchange) {
            $order->setState($order::STATE_PROCESSING, true);
        }

        return $this;
    }

    /**
     * Save current order statuses when order amendment is created.
     *
     * @param Varien_Event_Observer $observer
     * @throws Exception
     * @return $this
     */
    public function updateOrderStatuses(Varien_Event_Observer $observer)
    {
        parent::updateOrderStatuses($observer);
        Mage::dispatchEvent(
            'peppermint_reorder_submit_after_all',
            [
                'new_order' => $observer->getNewOrder(),
                'old_order' => $observer->getOldOrder(),
                'quote' => $observer->getQuote()
            ]
        );

        return $this;
    }

    /**
     * Send order status notification email on order cancel.
     * Event: order_cancel_after
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function sendOrderCancelEmail(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()
            ->getOrder();

        if (!Mage::registry('no_cancel_email')) {
            Mage::getModel('rockar_orderstatus/order')->setOrderId($order->getId())
                ->sendCancelNotification(false, Mage::helper('sales')->__('Canceled'));
        }
    }
}

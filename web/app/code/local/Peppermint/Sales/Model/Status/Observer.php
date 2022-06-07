<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Orderstatus
 * @author    Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Sales_Model_Status_Observer
{
    /**
     * Remember previous order status before order save
     * Event: sales_order_save_before
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function rememberPreviousStateAndStatus(Varien_Event_Observer $observer)
    {
        /** @var Peppermint_Sales_Model_Order $order */
        $order = $observer->getOrder();
        $originalData = $order->getOrigData();

        $order->setOldState($originalData['state'])
            ->setOldStatus($originalData['status']);

        return $this;
    }

    /**
     * Populate completed_on date
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function setCompletedOnDate(Varien_Event_Observer $observer)
    {
        /** @var Peppermint_Sales_Model_Order $order */
        $order = $observer->getEvent()->getOrder();
        $newStatus = $order->getData('status');
        $oldStatus = $order->getOrigData('status');

        if ($newStatus !== $oldStatus
            && $newStatus === Peppermint_Sales_Model_Order::STATUS_ORDER_COMPLETE
            || ($newStatus === Peppermint_Sales_Model_Order::STATUS_INVOICE_SUBMITTED
            && $oldStatus === Peppermint_Sales_Model_Order::STATUS_INVOICE_SUBMITTED)
        ) {
            $order->setData('completed_on', Varien_Date::now());
        }

        return $this;
    }

    /**
     * On checkout check and compare order state or status before and current
     * If change dispatch an pep status change event
     * Event: checkout_submit_all_after
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function checkStateAndStatusChanged(Varien_Event_Observer $observer)
    {
        /** @var Peppermint_Sales_Model_Order $order */
        $order = $observer->getOrder();

        if ($order->getOldState() !== $order->getState()
            || $order->getOldStatus() !== $order->getStatus()
        ) {
            Mage::dispatchEvent('peppermint_sales_order_state_status_changed', ['order' => $order]);
        }

        return $this;
    }
}

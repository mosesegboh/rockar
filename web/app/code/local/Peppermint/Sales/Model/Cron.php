<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Osama Ahmed <osama.ahmed@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Sales_Model_Cron
{
    /**
     * Updates the order grid so all orders are displayed
     *
     * @return void
     */
    public function run()
    {
        $coreResource = Mage::getSingleton('core/resource');

        $query = 'select entity_id from ' . $coreResource->getTableName('sales/order') . ' where entity_id not in (
            select entity_id from ' . $coreResource->getTableName('sales/order_grid') .
        ') group by entity_id';

        $queryIds = $coreResource->getConnection('core_read')->fetchAll($query);
        $orderIds = [];

        if ($queryIds) {
            foreach ($queryIds as ['entity_id' => $id]) {
                $orderIds[] = $id;
            }
        }

        if ($orderIds) {
            try {
                Mage::getModel('sales/order')->getResource()
                    ->updateGridRecords($orderIds);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }

    /**
     * Resend fail sap order
     *
     * @return void
     */
    public function sapResend()
    {
        // Need to set admin user to update order status comment
        Mage::getSingleton('admin/session')->setUser(Mage::getModel('admin/user')->setUsername('cron'));
        $failOrders = Mage::getModel('peppermint_sales/sap_fail')->getCollection()
            ->addFieldToSelect('order_id');

        $helper = Mage::helper('peppermint_sales/order');
        $orderModel = Mage::getModel('sales/order');

        foreach ($failOrders as $order) {
            $helper->prepareAndPushOrderToDs($orderModel->load($order->getOrderId()));
            $orderModel->unsetData();
        }
    }
}

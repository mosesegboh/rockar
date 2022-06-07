<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Orderstatus
 * @author    Jiraphong witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

/** @var Mage_Sales_Model_Entity_Setup $installer */
$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();

$rockarOrderStatusLabel = 'Order complete';

$statusToMap = [
    Peppermint_Sales_Model_Order::STATUS_ORDER_COMPLETE,
    Peppermint_Sales_Model_Order::STATUS_ORDER_CANCELLED,
    Peppermint_Sales_Model_Order::STATUS_ORDER_CLOSED
];

$rockarOrderStatusId = Mage::getModel('rockar_orderstatus/status')->load($rockarOrderStatusLabel, 'label')
    ->getEntityId();

$salesOrderStatus = Mage::getModel('sales/order_status');

if ($rockarOrderStatusId) {
    foreach ($statusToMap as $status) {
        $orderStatus = $salesOrderStatus->load($status, 'status');

        if ($orderStatus->getStatus() === $status) {
            $connection->insert(
                $installer->getTable('peppermint_orderstatus/status_mapping'),
                [
                    'orderstatus_id' => $rockarOrderStatusId,
                    'order_status' => $status
                ]
            );
        }
    }
}

$installer->endSetup();
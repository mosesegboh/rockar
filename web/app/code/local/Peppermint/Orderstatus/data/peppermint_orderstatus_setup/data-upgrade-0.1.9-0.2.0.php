<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Orderstatus
 * @author    Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

/** @var Mage_Sales_Model_Entity_Setup $installer */
$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();

$orderStatusStateTable = $installer->getTable('sales/order_status_state');
$connection->update($orderStatusStateTable, ['state' => 'canceled'], $connection->quoteInto('state=?', 'cancelled'));

$configUpdates = [
    'order_amend/order_status/accepted_amendment_order_status' => 'order_placed',
    'order_amend/order_status/awaiting_amendment_admin_status' => 7,
    'order_amend/order_status/awaiting_amendment_customer_status' => 7,
    'order_amend/order_status/cancelled_admin_status' => 6,
    'order_amend/order_status/order_can_be_amended' => 'vehicle_reserved,vehicle_arrived,order_placed,ready_for_collection,vehicle_in_transit'
];

foreach ($configUpdates as $path => $value) {
    $installer->setConfigData($path, $value);
}

$installer->endSetup();

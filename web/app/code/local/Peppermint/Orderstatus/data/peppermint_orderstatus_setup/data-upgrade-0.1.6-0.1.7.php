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

$orderStatusTable = $installer->getTable('sales/order_status');
$orderStatusStateTable = $installer->getTable('sales/order_status_state');

$connection->truncateTable($orderStatusTable);
$connection->truncateTable($orderStatusStateTable);

$orderStatuses = [
    [
        'status' => 'vehicle_reserved',
        'label' => 'Vehicle Reserved'
    ],
    [
        'status' => 'order_placed',
        'label' => 'Order Placed'
    ],
    [
        'status' => 'vehicle_in_transit',
        'label' => 'Vehicle In Transit'
    ],
    [
        'status' => 'vehicle_arrived',
        'label' => 'Arrived'
    ],
    [
        'status' => 'ready_for_collection',
        'label' => 'Ready For Collection'
    ],
    [
        'status' => 'order_complete',
        'label' => 'Order Complete'
    ],
    [
        'status' => 'order_cancelled',
        'label' => 'Order Cancelled'
    ],
    [
        'status' => 'order_closed',
        'label' => 'Order Closed'
    ],
    [
        'status' => 'pending_amendment',
        'label' => 'Pending Amendment'
    ]
];
$connection->insertMultiple($orderStatusTable, $orderStatuses);

$orderStatusStates = [
    [
        'status' => 'vehicle_reserved',
        'state' => 'new',
        'is_default' => 1
    ],
    [
        'status' => 'order_placed',
        'state' => 'processing',
        'is_default' => 1
    ],
    [
        'status' => 'vehicle_in_transit',
        'state' => 'processing',
        'is_default' => 0
    ],
    [
        'status' => 'vehicle_arrived',
        'state' => 'processing',
        'is_default' => 0
    ],
    [
        'status' => 'ready_for_collection',
        'state' => 'processing',
        'is_default' => 0
    ],
    [
        'status' => 'order_complete',
        'state' => 'complete',
        'is_default' => 1
    ],
    [
        'status' => 'order_cancelled',
        'state' => 'cancelled',
        'is_default' => 1
    ],
    [
        'status' => 'order_closed',
        'state' => 'closed',
        'is_default' => 1
    ],
    [
        'status' => 'pending_amendment',
        'state' => 'holded',
        'is_default' => 1
    ]
];
$connection->insertMultiple($orderStatusStateTable, $orderStatusStates);

$installer->endSetup();

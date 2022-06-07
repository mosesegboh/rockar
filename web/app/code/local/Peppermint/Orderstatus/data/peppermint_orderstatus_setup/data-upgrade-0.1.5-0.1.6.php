<?php
/**
 * @category     Peppermint
 * @package      Peppermint_Orderstatus
 * @author       Dinu Brie <dinu.brie@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();
$newDefaultStates = [
    [
        'status' => 'vehicle_reserved',
        'state' => 'pending',
        'is_default' => '1'
    ],
    [
        'status' => 'pending_amendment',
        'state' => 'on_hold',
        'is_default' => '1'
    ],
    [
        'status' => 'order_placed',
        'state' => 'processing',
        'is_default' => '1'
    ],
    [
        'status' => 'vehicle_in_transit',
        'state' => 'processing',
        'is_default' => '0'
    ],
    [
        'status' => 'arrived',
        'state' => 'processing',
        'is_default' => '0'
    ],
    [
        'status' => 'ready_for_collection',
        'state' => 'processing',
        'is_default' => '0'
    ],
    [
        'status' => 'order_complete',
        'state' => 'complete',
        'is_default' => '1'
    ],
    [
        'status' => 'cancelled',
        'state' => 'cancelled',
        'is_default' => '1'
    ]
];
$codes = [
    [
        'code' => 'vehicle_reserved',
        'position' => 1
    ],
    [
        'code' => 'order_placed',
        'position' => 2
    ],
    [
        'code' => 'vehicle_in_transit',
        'position' => 3
    ],
    [
        'code' => 'vehicle_arrived',
        'position' => 4
    ],
    [
        'code' => 'ready_for_collection',
        'position' => 5
    ],
    [
        'code' => 'order_complete',
        'position' => 6
    ],
    [
        'code' => 'pending_amendment',
        'position' => 7
    ]
];
try {
    $statusCode = Mage::getModel('rockar_orderstatus/status');
    foreach ($codes as $code) {
        $statusCode->load($code['position'], 'position');
        $statusCode->setCode($code['code'])
            ->save();
    }

    /* fix for previous data upgrade typo */
    Mage::getModel('sales/order_status')->load('Arrived', 'label')
        ->setLabel('Vehicle in transit')
        ->save();
} catch (Exception $e) {
    Mage::logException($e);
}
$orderStatusState = $installer->getTable('sales/order_status_state');
$connection->truncateTable($orderStatusState);
$connection->insertMultiple($orderStatusState, $newDefaultStates);
$installer->endSetup();

<?php
/**
 * @category     Peppermint
 * @package      Peppermint_Orderstatus
 * @author       Dinu Brie <dinu.brie@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

$this->startSetup();
$orderCodes = [
    [
        'status' => 'vehicle_reserved',
        'state' => 'pending',
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
        'is_default' => '1'
    ],
    [
        'status' => 'ready_for_collection',
        'state' => 'processing',
        'is_default' => '1'
    ],
    [
        'status' => 'order_complete',
        'state' => 'complete',
        'is_default' => '1'
    ],
    [
        'status' => 'order_cancelled',
        'state' => 'cancelled',
        'is_default' => '1'
    ],
    [
        'status' => 'order_closed',
        'state' => 'closed',
        'is_default' => '1'
    ],
    [
        'status' => 'pending_amendment',
        'state' => 'on_hold',
        'is_default' => '1'
    ]
];

try {
    $statusStateTable = $this->getTable('sales/order_status_state');
    $this->getConnection()->insertArray(
        $statusStateTable,
        [
            'status',
            'state',
            'is_default'
        ],
        $orderCodes
    );
} catch (Exception $e) {
    Mage::logException($e);
}
$this->endSetup();

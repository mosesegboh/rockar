<?php
/**
 * @category     Peppermint
 * @package      Peppermint_Orderstatus
 * @author       Dinu Brie <dinu.brie@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

/* @var Rockar_Orderstatus_Model_Status $orderStatus */
$orderStatus = Mage::getModel('rockar_orderstatus/status')->getCollection();
$this->startSetup();
foreach ($orderStatus as $status) {
    try {
        $status->delete();
    } catch (Exception $e) {
        Mage::logException($e);
    }
}

$statusLabels = [
    [
        'label' => 'Vehicle Reserved',
        'description' => 'Customer generates quote for trade-in',
        'position' => 1
    ],
    [
        'label' => 'Order Placed',
        'description' => 'Customer clicks \'order placed\' without trade-in, and \'accepts final trade-in value\' with trade-in (this is generation of OTP)',
        'position' => 2
    ],
    [
        'label' => 'Vehicle In Transit',
        'description' => 'Retailer clicks \'confirm delivery\' in SMS -finance approved',
        'position' => 3
    ],
    [
        'label' => 'Arrived',
        'description' => 'Dealer acknowledges car has arrived at dealership via SMS',
        'position' => 4
    ],
    [
        'label' => 'Ready For Collection',
        'description' => 'Dealer manually changes customer facing status from arrived to ready for collection',
        'position' => 5
    ],
    [
        'label' => 'Order complete',
        'description' => 'Dealer invoices the order by clicking the \'invoice\' buttonin DSP & \'Ship\'NOTE:\'Invoice\' button sends invoice information to DS; this is not a status',
        'position' => 6
    ],
    [
        'label' => 'Pending Amendment',
        'description' => 'Dealer clicks \'amend\' order in DSP',
        'position' => 7
    ]
];
$salesOrderStatuses = [
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
try {
    foreach ($statusLabels as $status) {
        Mage::getModel('rockar_orderstatus/status')->addData($status)
            ->setIsActive(1)
            ->setStores([0])
            ->save();
    }
    foreach ($salesOrderStatuses as $code) {
        Mage::getModel('sales/order_status')->addData($code)
            ->save();
    }
} catch (Exception $e) {
    Mage::logException($e);
}
$this->endSetup();

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Checkout
 * @author    Ketevani Revazishvili <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

$this->startSetup();

try {
    $storeIdentifiers = [
        'bmw_store_view',
        'mini_store_view',
        'motorrad_store_view'
    ];

    //get id for each store code
    $storeCodes = [];

    foreach ($storeIdentifiers as $id) {
        $storeCodes[$id] = Mage::getSingleton('core/store')->load($id, 'code')->getId();
    }

    $cmsBlocks = [
        [
            'identifier' => 'customer_order_cap',
            'title' => 'Customer Order Cap',
            'content' => '<h1>You\'ve reached the maximum limit of pending/active orders</h1>
                <p>To place a new order, you\'ll need to conclude an existing order or request assistance from your selected retailer. </p>',
            'stores' => $storeCodes['bmw_store_view'],
            'is_active' => 1
        ],
        [
            'identifier' => 'customer_order_cap',
            'title' => 'Customer Order Cap MINI',
            'content' => '<h1>You\'ve reached the maximum limit of pending/active orders</h1>
                <p>To place a new order, you\'ll need to conclude an existing order or request assistance from your selected retailer. </p>',
            'stores' => $storeCodes['mini_store_view'],
            'is_active' => 1
        ],
        [
            'identifier' => 'customer_order_cap',
            'title' => 'Customer Order Cap MOTORRAD',
            'content' => '<h1>You\'ve reached the maximum limit of pending/active orders</h1>
                <p>To place a new order, you\'ll need to conclude an existing order or request assistance from your selected retailer. </p>',
            'stores' => $storeCodes['motorrad_store_view'],
            'is_active' => 1
        ]
    ];

     foreach ($cmsBlocks as $blockData) {
        Mage::getModel('cms/block')
            ->setStoreId($blockData['stores'])
            ->load($blockData['identifier'], 'identifier')
            ->addData($blockData)
            ->save();
    }

} catch (Exception $e) {
    Mage::logException($e);
}

$this->endSetup();

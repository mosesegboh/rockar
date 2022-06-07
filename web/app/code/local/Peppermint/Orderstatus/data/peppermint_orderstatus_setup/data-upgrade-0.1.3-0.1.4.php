<?php
/**
 * @category     Peppermint
 * @package      Peppermint_Orderstatus
 * @author       Dinu Brie <dinu.brie@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

$this->startSetup();
$descriptionByBrand = [
    [
        'store_id' => 2,
        'label' => 'Vehicle Reserved',
        'description' => 'We\'ve reserved your vehicle while we finalise your order. You\'ll receive regular updates about the progress of your order as well as requests for potential documents - so please keep an eye out for our emails over the next few days.',
        'is_active' => 1,
        'show_in_frontend' => 1,
        'code' => 'vehicle_reserved'
    ],
    [
        'store_id' => 2,
        'label' => 'Order Placed',
        'description' => 'Great - we\'ve received your BMW order. You\'ll receive regular updates about the vehicle’s progress as well as requests for potential documents - so please keep an eye out for our emails over the next few days.',
        'is_active' => 1,
        'position' => 2,
        'show_in_frontend' => 1,
        'code' => 'order_placed'
    ],
    [
        'store_id' => 2,
        'label' => 'Vehicle In Transit',
        'description' => 'Your vehicle has officially begun its journey to your preferred BMW Retailer and we\'ll let you know the moment it arrives. In the meantime, please review your order to make sure it\' s correct and up to date.',
        'is_active' => 1,
        'position' => 3,
        'show_in_frontend' => 1,
        'code' => 'vehicle_in_transit'
    ],
    [
        'store_id' => 2,
        'label' => 'Arrived',
        'description' => 'Great news. Your new vehicle has just arrived at your BMW Retailer and it\'s almost time start your new journey. We’ll let you know when it\'s ready for collection.',
        'is_active' => 1,
        'position' => 4,
        'show_in_frontend' => 1,
        'code' => 'vehicle_arrived'
    ],
    [
        'store_id' => 2,
        'label' => 'Ready For Collection',
        'description' => 'The day has finally arrived. Your new vehicle is ready for collection at your BMW Retailer.',
        'is_active' => 1,
        'position' => 5,
        'show_in_frontend' => 1,
        'code' => 'ready_for_collection'
    ],
    [
        'store_id' => 2,
        'label' => 'Order Complete',
        'description' => 'Congratulations. According to our records, you\'ve collected your vehicle from your BMW Retailer and are currently enjoying the Sheer Driving Pleasure of your new BMW.',
        'is_active' => 1,
        'position' => 6,
        'show_in_frontend' => 1,
        'code' => 'order_complete'
    ],
    [
        'store_id' => 2,
        'label' => 'Pending Amendment',
        'description' => 'Your order is on hold. Please visit your preferred Retailer to continue with you order.',
        'is_active' => 1,
        'position' => 7,
        'show_in_frontend' => 1,
        'code' => 'pending_amendment'
    ],
    [
        'store_id' => 3,
        'label' => 'Vehicle Reserved',
        'description' => 'We\'ve reserved your MINI while we finalise your order. You\'ll receive regular updates about the progress of your order as well as requests for potential documents - so please keep an eye out for our emails over the next few days.',
        'is_active' => 1,
        'position' => 1,
        'show_in_frontend' => 1,
        'code' => 'vehicle_reserved'
    ],
    [
        'store_id' => 3,
        'label' => 'Order Placed',
        'description' => 'Great - we\'ve received your MINI order. You\'ll receive regular updates about the vehicle’s progress as well as requests for potential documents - so please keep an eye out for our emails over the next few days.',
        'is_active' => 1,
        'position' => 2,
        'show_in_frontend' => 1,
        'code' => 'order_placed'
    ],
    [
        'store_id' => 3,
        'label' => 'MINI In Transit',
        'description' => 'Your MINI has officially begun its journey to your preferred MINI Retailer and we\'ll let you know the moment it arrives. In the meantime, please review your order to make sure it\' s correct and up to date.',
        'is_active' => 1,
        'position' => 3,
        'show_in_frontend' => 1,
        'code' => 'vehicle_in_transit'
    ],
    [
        'store_id' => 3,
        'label' => 'Arrived',
        'description' => 'Great news. Your new MINI has just arrived at your MINI Retailer and it\'s almost time start your new adventure. We\'ll let you know when it\' s ready for collection.',
        'is_active' => 1,
        'position' => 4,
        'show_in_frontend' => 1,
        'code' => 'vehicle_arrived'
    ],
    [
        'store_id' => 3,
        'label' => 'Ready For Collection',
        'description' => 'The day has finally arrived. Your new MINI is ready for collection at your MINI Retailer.',
        'is_active' => 1,
        'position' => 5,
        'show_in_frontend' => 1,
        'code' => 'ready_for_collection'
    ],
    [
        'store_id' => 3,
        'label' => 'Order Complete',
        'description' => 'Congrats. According to our records, you\'ve collected your car from your MINI Retailer and enjoying the driving fun of your new MINI.',
        'is_active' => 1,
        'position' => 6,
        'show_in_frontend' => 1,
        'code' => 'order_complete'
    ],
    [
        'store_id' => 3,
        'label' => 'Pending Amendment',
        'description' => 'Your order is on hold. Please visit your preferred Retailer to continue with you order.',
        'is_active' => 1,
        'position' => 7,
        'show_in_frontend' => 1,
        'code' => 'pending_amendment'
    ],
    [
        'store_id' => 4,
        'label' => 'Vehicle Reserved',
        'description' => 'We\'ve reserved your BMW motorcycle while we finalise your order. You\'ll receive regular updates about the progress of your order as well as requests for potential documents - so please keep an eye out for our emails over the next few day',
        'is_active' => 1,
        'position' => 1,
        'show_in_frontend' => 1,
        'code' => 'vehicle_reserved'
    ],
    [
        'store_id' => 4,
        'label' => 'Order Placed',
        'description' => 'Great - we\'ve received your BMW motorcycle order. You\'ll receive regular updates about the vehicle\'s progress as well as requests for potential documents - so please keep an eye out for our emails over the next few days.',
        'is_active' => 1,
        'position' => 2,
        'show_in_frontend' => 1,
        'code' => 'order_placed'
    ],
    [
        'store_id' => 4,
        'label' => 'Motorcycle in transit',
        'description' => 'Your motorcycle has officially begun its journey to your preferred BMW Motorrad Retailer and we\'ll let you know the moment it arrives. In the meantime, please review your order to make sure it\' s correct and up to date.',
        'is_active' => 1,
        'position' => 3,
        'show_in_frontend' => 1,
        'code' => 'vehicle_in_transit'
    ],
    [
        'store_id' => 4,
        'label' => 'Arrived',
        'description' => 'Great news. Your new BMW motorcycle has just arrived at your BMW Motorrad Retailer and it\'s almost time start your new adventure. We\'ll let you know when it\' s ready for collection.',
        'is_active' => 1,
        'position' => 4,
        'show_in_frontend' => 1,
        'code' => 'vehicle_arrived'
    ],
    [
        'store_id' => 4,
        'label' => 'Ready For Collection',
        'description' => 'The day has finally arrived. Your new BMW motorcycle is ready for collection at your BMW Motorrad Retailer.',
        'is_active' => 1,
        'position' => 5,
        'show_in_frontend' => 1,
        'code' => 'ready_for_collection'
    ],
    [
        'store_id' => 4,
        'label' => 'Order Complete',
        'description' => 'Congratulations. According to our records, you\'ve collected your BMW motorcycle. We hope you are enjoying your new freedom to Make Life a Ride.',
        'is_active' => 1,
        'position' => 6,
        'show_in_frontend' => 1,
        'code' => 'order_complete'
    ],
    [
        'store_id' => 4,
        'label' => 'Pending Amendment',
        'description' => 'Your order is on hold. Please visit your preferred Retailer to continue with you order.',
        'is_active' => 1,
        'position' => 7,
        'show_in_frontend' => 1,
        'code' => 'pending_amendment'
    ]
];

try {
    foreach ($descriptionByBrand as $brands) {
        Mage::getModel('rockar_orderstatus/status')->setData($brands)
            ->setStores([$brands['store_id']])
            ->save();
    }
} catch (Exception $e) {
    Mage::logException($e);
}
$this->endSetup();

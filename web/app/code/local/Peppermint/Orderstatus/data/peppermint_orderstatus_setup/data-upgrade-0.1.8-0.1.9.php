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

$rockarStatusTable = $installer->getTable('rockar_orderstatus/status');
$rockarStatusOrderTable = $installer->getTable('rockar_orderstatus/status_order');
$rockarStatusStoreTable = $installer->getTable('rockar_orderstatus/status_store');
$mappingTableName = $installer->getTable('peppermint_orderstatus/status_mapping');

$connection->truncateTable($rockarStatusStoreTable);
$connection->truncateTable($rockarStatusOrderTable);
$connection->truncateTable($rockarStatusTable);

$connection->insertMultiple(
    $rockarStatusTable,
    [
        [
            'label' => 'Vehicle Reserved',
            'description' => 'Customer generates quote for trade-in',
            'position' => 1,
            'is_active' => 1,
            'store_id' => Mage_Core_Model_App::ADMIN_STORE_ID
        ],
        [
            'label' => 'Order Placed',
            'description' => 'Customer clicks \'order placed\' without trade-in, and \'accepts final trade-in value\' with trade-in (this is generation of OTP)',
            'position' => 2,
            'is_active' => 1,
            'store_id' => Mage_Core_Model_App::ADMIN_STORE_ID
        ],
        [
            'label' => 'Vehicle In Transit',
            'description' => 'Retailer clicks \'confirm delivery\' in SMS -finance approved',
            'position' => 3,
            'is_active' => 1,
            'store_id' => Mage_Core_Model_App::ADMIN_STORE_ID
        ],
        [
            'label' => 'Arrived',
            'description' => 'Dealer acknowledges car has arrived at dealership via SMS',
            'position' => 4,
            'is_active' => 1,
            'store_id' => Mage_Core_Model_App::ADMIN_STORE_ID
        ],
        [
            'label' => 'Ready For Collection',
            'description' => 'Dealer manually changes customer facing status from arrived to ready for collection',
            'position' => 5,
            'is_active' => 1,
            'store_id' => Mage_Core_Model_App::ADMIN_STORE_ID
        ],
        [
            'label' => 'Order complete',
            'description' => 'Dealer invoices the order by clicking the \'invoice\' buttonin DSP & \'Ship\'NOTE:\'Invoice\' button sends invoice information to DS; this is not a status',
            'position' => 6,
            'is_active' => 1,
            'store_id' => Mage_Core_Model_App::ADMIN_STORE_ID
        ],
        [
            'label' => 'Pending Amendment',
            'description' => 'Dealer clicks \'amend\' order in DSP',
            'position' => 7,
            'is_active' => 1,
            'store_id' => Mage_Core_Model_App::ADMIN_STORE_ID
        ]
    ]
);

$connection->insertMultiple(
    $rockarStatusStoreTable,
    [
        [
            'orderstatus_id' => 1,
            'store_id' => 2,
            'label' => 'Vehicle Reserved',
            'description' => 'We\'ve reserved your vehicle while we finalise your order. You\'ll receive regular updates about the progress of your order as well as requests for potential documents - so please keep an eye out for our emails over the next few days.'
        ],
        [
            'orderstatus_id' => 2,
            'store_id' => 2,
            'label' => 'Order Placed',
            'description' => 'Great - we\'ve received your BMW order. You\'ll receive regular updates about the vehicle’s progress as well as requests for potential documents - so please keep an eye out for our emails over the next few days.'
        ],
        [
            'orderstatus_id' => 3,
            'store_id' => 2,
            'label' => 'Vehicle In Transit',
            'description' => 'Your vehicle has officially begun its journey to your preferred BMW Retailer and we\'ll let you know the moment it arrives. In the meantime, please review your order to make sure it\' s correct and up to date.'
        ],
        [
            'orderstatus_id' => 4,
            'store_id' => 2,
            'label' => 'Arrived',
            'description' => 'Great news. Your new vehicle has just arrived at your BMW Retailer and it\'s almost time start your new journey. We’ll let you know when it\'s ready for collection.'
        ],
        [
            'orderstatus_id' => 5,
            'store_id' => 2,
            'label' => 'Ready For Collection',
            'description' => 'The day has finally arrived. Your new vehicle is ready for collection at your BMW Retailer.'
        ],
        [
            'orderstatus_id' => 6,
            'store_id' => 2,
            'label' => 'Order Complete',
            'description' => 'Congratulations. According to our records, you\'ve collected your vehicle from your BMW Retailer and are currently enjoying the Sheer Driving Pleasure of your new BMW.'
        ],
        [
            'orderstatus_id' => 7,
            'store_id' => 2,
            'label' => 'Pending Amendment',
            'description' => 'Your order is on hold. Please visit your preferred Retailer to continue with you order.'
        ],
        [
            'orderstatus_id' => 1,
            'store_id' => 3,
            'label' => 'Vehicle Reserved',
            'description' => 'We\'ve reserved your MINI while we finalise your order. You\'ll receive regular updates about the progress of your order as well as requests for potential documents - so please keep an eye out for our emails over the next few days.'
        ],
        [
            'orderstatus_id' => 2,
            'store_id' => 3,
            'label' => 'Order Placed',
            'description' => 'Great - we\'ve received your MINI order. You\'ll receive regular updates about the vehicle’s progress as well as requests for potential documents - so please keep an eye out for our emails over the next few days.'
        ],
        [
            'orderstatus_id' => 3,
            'store_id' => 3,
            'label' => 'MINI In Transit',
            'description' => 'Your MINI has officially begun its journey to your preferred MINI Retailer and we\'ll let you know the moment it arrives. In the meantime, please review your order to make sure it\' s correct and up to date.'
        ],
        [
            'orderstatus_id' => 4,
            'store_id' => 3,
            'label' => 'Arrived',
            'description' => 'Great news. Your new MINI has just arrived at your MINI Retailer and it\'s almost time start your new adventure. We\'ll let you know when it\' s ready for collection.'
        ],
        [
            'orderstatus_id' => 5,
            'store_id' => 3,
            'label' => 'Ready For Collection',
            'description' => 'The day has finally arrived. Your new MINI is ready for collection at your MINI Retailer.'
        ],
        [
            'orderstatus_id' => 6,
            'store_id' => 3,
            'label' => 'Order Complete',
            'description' => 'Congrats. According to our records, you\'ve collected your car from your MINI Retailer and enjoying the driving fun of your new MINI.'
        ],
        [
            'orderstatus_id' => 7,
            'store_id' => 3,
            'label' => 'Pending Amendment',
            'description' => 'Your order is on hold. Please visit your preferred Retailer to continue with you order.'
        ],
        [
            'orderstatus_id' => 1,
            'store_id' => 4,
            'label' => 'Vehicle Reserved',
            'description' => 'We\'ve reserved your BMW motorcycle while we finalise your order. You\'ll receive regular updates about the progress of your order as well as requests for potential documents - so please keep an eye out for our emails over the next few day'
        ],
        [
            'orderstatus_id' => 2,
            'store_id' => 4,
            'label' => 'Order Placed',
            'description' => 'Great - we\'ve received your BMW motorcycle order. You\'ll receive regular updates about the vehicle\'s progress as well as requests for potential documents - so please keep an eye out for our emails over the next few days.'
        ],
        [
            'orderstatus_id' => 3,
            'store_id' => 4,
            'label' => 'Motorcycle in transit',
            'description' => 'Your motorcycle has officially begun its journey to your preferred BMW Motorrad Retailer and we\'ll let you know the moment it arrives. In the meantime, please review your order to make sure it\' s correct and up to date.'
        ],
        [
            'orderstatus_id' => 4,
            'store_id' => 4,
            'label' => 'Arrived',
            'description' => 'Great news. Your new BMW motorcycle has just arrived at your BMW Motorrad Retailer and it\'s almost time start your new adventure. We\'ll let you know when it\' s ready for collection.'
        ],
        [
            'orderstatus_id' => 5,
            'store_id' => 4,
            'label' => 'Ready For Collection',
            'description' => 'The day has finally arrived. Your new BMW motorcycle is ready for collection at your BMW Motorrad Retailer.'
        ],
        [
            'orderstatus_id' => 6,
            'store_id' => 4,
            'label' => 'Order Complete',
            'description' => 'Congratulations. According to our records, you\'ve collected your BMW motorcycle. We hope you are enjoying your new freedom to Make Life a Ride.'
        ],
        [
            'orderstatus_id' => 7,
            'store_id' => 4,
            'label' => 'Pending Amendment',
            'description' => 'Your order is on hold. Please visit your preferred Retailer to continue with you order.'
        ]
    ]
);

$connection->insertMultiple(
    $mappingTableName,
    [
        [
            'orderstatus_id' => 1,
            'order_status' => 'vehicle_reserved'
        ],
        [
            'orderstatus_id' => 2,
            'order_status' => 'order_placed'
        ],
        [
            'orderstatus_id' => 3,
            'order_status' => 'vehicle_in_transit'
        ],
        [
            'orderstatus_id' => 4,
            'order_status' => 'vehicle_arrived'
        ],
        [
            'orderstatus_id' => 5,
            'order_status' => 'ready_for_collection'
        ],
        [
            'orderstatus_id' => 6,
            'order_status' => 'order_complete'
        ],
        [
            'orderstatus_id' => 6,
            'order_status' => 'order_cancelled'
        ],
        [
            'orderstatus_id' => 6,
            'order_status' => 'order_closed'
        ],
        [
            'orderstatus_id' => 7,
            'order_status' => 'pending_amendment'
        ]
    ]
);

$installer->endSetup();

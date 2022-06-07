<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Checkout
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */
/** @var Mage_Sales_Model_Entity_Setup $installer */
$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();

$productExpiredBlocks = [
    'product_expired_changed_price' =>[
        'title' => 'Please review the product data changes',
        'identifier' => 'product_expired_changed_price',
        'content' => 'Available product has been updated and price changed from {{var before}} to {{var after}}',
        'stores' => 0,
        'is_active' => 1
    ],
    'product_expired_changed_leadtime' =>[
        'title' => 'Please review the product data changes',
        'identifier' => 'product_expired_changed_leadtime',
        'content' => 'Available product has been updated and lead time changed from {{var before}} to {{var after}}',
        'stores' => 0,
        'is_active' => 1
    ],
    'product_expired_no_replace' =>[
        'title' => 'Product is not longer available',
        'identifier' => 'product_expired_no_replace',
        'content' => 'Product is not available',
        'stores' => 0,
        'is_active' => 1
    ]
];

foreach ($productExpiredBlocks as $identifier => $blockData) {
    Mage::getModel('cms/block')->load($identifier)
        ->addData($blockData)
        ->save();
}

$installer->endSetup();

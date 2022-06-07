<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Ketevani Revazishvili <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

/** @var $this Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$salesOrderItem = $installer->getTable('sales/order_item');
$installer->getConnection()
    ->addColumn($salesOrderItem, 'pricing_details_snapshot', [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'identity' => false,
        'nullable' => true,
        'primary' => false,
        'comment' => 'Pricing Details Snapshot'
    ]);

$installer->endSetup();

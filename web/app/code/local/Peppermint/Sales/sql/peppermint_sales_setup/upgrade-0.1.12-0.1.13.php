<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Ausma Smite <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

/** @var $this Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$orderTable = $installer->getTable('sales/order_item');
$installer->getConnection()
    ->addColumn($orderTable, 'published_to_ds_date', [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'identity' => false,
        'nullable' => true,
        'primary' => false,
        'comment' => 'Published To DS Date'
    ]);

$installer->endSetup();

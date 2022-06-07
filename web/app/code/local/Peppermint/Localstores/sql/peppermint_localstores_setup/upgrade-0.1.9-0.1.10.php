<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Localstores
 * @author    Ausma Smite <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 *
 * @var $this Mage_Core_Model_Resource_Setup
 */

$connection = $this->getConnection();
$installer = new Mage_Sales_Model_Resource_Setup('sales_setup');
$installer->startSetup();

/* Add column to sales_flat_order table */
$tableName = $installer->getTable('sales/order');
$definition = [
    'type' => Varien_Db_Ddl_Table::TYPE_VARCHAR,
    'comment' => 'Dealer ID',
    'length' => 255,
    'nullable' => true,
    'grid' => true
];
$columnCode = 'dealer_id';

if (!$connection->tableColumnExists($tableName, $columnCode)) {
    $installer->addAttribute('order', $columnCode, $definition);
}

$installer->endSetup();

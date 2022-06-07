<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();

// Add vin number column to sales_flat_order table
$tableName = $installer->getTable('sales/order');
$definition = [
    Varien_Db_Ddl_Table::TYPE_VARCHAR,
    'length' => 255,
    'comment' => 'VIN Number',
    'nullable' => true,
    'default' => null,
    'grid' => true
];
$columnCode = 'vin_number';

if (!$connection->tableColumnExists($tableName, $columnCode)) {
    $installer->addAttribute('order', $columnCode, $definition);
}

$installer->endSetup();

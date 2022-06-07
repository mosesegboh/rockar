<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Taras Kapushchak <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();

// Add credit app ID and status columns to sales_flat_order table
$tableName = $installer->getTable('sales/order');
$definitions = [
    'credit_app_id' => [
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        'length' => 32,
        'comment' => 'Credit App ID',
        'nullable' => true,
        'default' => null,
        'grid' => true,
    ],
    'credit_app_status' => [
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        'length' => 32,
        'comment' => 'Credit App Status',
        'nullable' => true,
        'default' => null,
        'grid' => true,
    ]
];

foreach ($definitions as $columnCode => $definition) {
    if (!$connection->tableColumnExists($tableName, $columnCode)) {
        $installer->addAttribute('order', $columnCode, $definition);
    }
}

$installer->endSetup();

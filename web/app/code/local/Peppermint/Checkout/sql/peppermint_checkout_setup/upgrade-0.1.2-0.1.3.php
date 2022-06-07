<?php

/**
* @category  Peppermint
* @package   Peppermint\Checkout
* @author    Lucian Mesaros <lucian.mesaros@rockar.com>
* @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
*/

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();

$tableNames = [
    $installer->getTable('sales/quote_item'),
    $installer->getTable('sales/order_item')
];

$columnsToAdd = [
    'deposit_source' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 5,
        'comment' => 'Cash Deposit Source Code'
    ],
    'deposit_other_description' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 100,
        'comment' => 'Cash Deposit Other Details'
    ]
];

foreach ($tableNames as $tableName) {
    foreach ($columnsToAdd as $columnName => $column) {
        if (!$connection->tableColumnExists($tableName, $columnName)) {
            $connection->addColumn($tableName, $columnName, $column);
        }
    }
}

$installer->endSetup();

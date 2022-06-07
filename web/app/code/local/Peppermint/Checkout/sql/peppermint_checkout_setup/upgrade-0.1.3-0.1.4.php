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
    $installer->getTable('rockar_checkout/quote_additional'),
    $installer->getTable('rockar_checkout/order_additional')
];

$columnsToAdd = [
    'preferred_communication_method' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 50,
        'comment' => 'Preferred Contact Method (Email/Post/SMS)'
    ],
    'contract_document' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 10,
        'comment' => 'Contract Documentation'
    ],
    'statement_frequency' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 10,
        'comment' => 'Statement Frequency'
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

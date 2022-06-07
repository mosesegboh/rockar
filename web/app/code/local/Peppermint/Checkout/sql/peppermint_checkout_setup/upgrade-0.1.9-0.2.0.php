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
    'pref_method_contact_email' => [
        'type' => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'comment' => 'Preferred Contact Method (Email)'
    ],
    'pref_method_contact_sms' => [
        'type' => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'comment' => 'Preferred Contact Method (SMS)'
    ],
    'pref_method_contact_normal' => [
        'type' => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'comment' => 'Preferred Contact Method (Normal Post)'
    ],
    'contract_document' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 1,
        'default' => '0',
        'comment' => 'Contract Documentation'
    ],
    'statement_frequency' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 1,
        'default' => '0',
        'comment' => 'Statement Frequency'
    ]
];

// drop obsolete column
foreach ($tableNames as $tableName) {
    $connection->dropColumn($tableName, 'preferred_communication_method');
}

foreach ($tableNames as $tableName) {
    foreach ($columnsToAdd as $columnName => $column) {
        if ($connection->tableColumnExists($tableName, $columnName)) {
            $connection->dropColumn($tableName, $columnName);
        }
        $connection->addColumn($tableName, $columnName, $column);
    }
}

$installer->endSetup();

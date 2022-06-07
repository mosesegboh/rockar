<?php
/**
 * @category     Peppermint
 * @package      Peppermint_Checkout
 * @author       Robert Ionas <robert.ionas@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

/** @var Mage_Core_Model_Resource_Setup $this */
$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();

$tableNames = [
    $installer->getTable('rockar_checkout/quote_additional'),
    $installer->getTable('rockar_checkout/order_additional')
];

$columnsToAdd = [
    'name_of_bank_account' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 100,
        'comment' => 'Name of bank account'
    ],
    'bank_name' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 100,
        'comment' => 'Bank name'
    ],
    'branch_name' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 100,
        'comment' => 'Branch name'
    ],
    'branch_code' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 20,
        'comment' => 'Branch code'
    ],
    'account_type_code' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 5,
        'comment' => 'Account Type Code'
    ]
];

foreach ($tableNames as $tableName) {
    foreach ($columnsToAdd as $column => $dataType) {
        if (!$connection->tableColumnExists($tableName, $column)) {
            $connection->addColumn($tableName, $column, $dataType);
        }
    }
}

$installer->endSetup();

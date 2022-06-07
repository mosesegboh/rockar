<?php
/**
* @category  Peppermint
* @package   Peppermint\Checkout
* @author    Adrian Grigorita <adrian.grigorita@rockar.com>
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
    'home_tel' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 15,
        'comment' => 'Home phone number'
    ],
    'gender' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 5,
        'comment' => 'Gender'
    ],
    'race' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 5,
        'comment' => 'Race'
    ],
    'preferred_language' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 5,
        'comment' => 'Preferred Language'
    ],
    'marriage_type' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 5,
        'comment' => 'Marriage Type'
    ],
    'spouse_first_name' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 100,
        'comment' => 'Spouse First Name'
    ],
    'spouse_last_name' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 100,
        'comment' => 'Spouse Last Name'
    ],
    'spouse_id_type' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 5,
        'comment' => 'Spouse ID Type'
    ],
    'spouse_id_no' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 20,
        'comment' => 'Spouse ID number'
    ],
    'spouse_cell_number' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 10,
        'comment' => 'Spouse Cell Number'
    ],
    'spouse_email' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 100,
        'comment' => 'Spouse Email'
    ],
    'kin_name' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 200,
        'comment' => 'Kin Name'
    ],
    'kin_tel' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 100,
        'comment' => 'Kin Tel'
    ],
    'spouse_consent' => [
        'type' => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'comment' => 'Spouse consent'
    ]
];

foreach ($tableNames as $tableName) {
    foreach ($columnsToAdd as $column => $dataType) {
        if ($connection->tableColumnExists($tableName, $column)) {
            $connection->dropColumn($tableName, $column);
        }
        $connection->addColumn($tableName, $column, $dataType);
    }
}

$installer->endSetup();

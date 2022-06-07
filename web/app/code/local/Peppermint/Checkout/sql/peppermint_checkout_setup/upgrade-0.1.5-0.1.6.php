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
    $installer->getTable('rockar_checkout/quote_additional_residence'),
    $installer->getTable('rockar_checkout/order_additional_residence')
];

$columnsToAdd = [
    'house_status' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 5,
        'comment' => 'Home status'
    ],
    'same_as_residential' => [
        'type' => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'comment' => 'Same as residential address'
    ],
    'address_2' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 100,
        'comment' => 'Address Line 2'
    ],
    'duration_at_current_address' => [
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'comment' => 'Current address in months'
    ],
    'duration_at_previous_address' => [
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'comment' => 'Previous address in months'
    ]
];

foreach ($tableNames as $tableName) {
    foreach ($columnsToAdd as $column => $dataType) {
        if (!$connection->tableColumnExists($tableName, $column)) {
            $connection->addColumn($tableName, $column, $dataType);
        }
    }
}

$columnsToModify = [
    'ownership' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 5
    ],
    'accommodation_type' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 5
    ]
];

foreach ($tableNames as $tableName) {
    foreach ($columnsToModify as $column => $dataType) {
        if ($connection->tableColumnExists($tableName, $column)) {
            $connection->modifyColumn($tableName, $column, $dataType);
        }
    }
}

// change column type
$columnsToUpdate = [
    'street' => [
        'new_name' => 'address_1',
        'definition' => [
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => 100,
            'comment' => 'Address line 1'
        ]
    ]
];

foreach ($tableNames as $tableName) {
    foreach ($columnsToUpdate as $oldColumnName => $columnData) {
        if ($connection->tableColumnExists($tableName, $oldColumnName)) {
            $connection->changeColumn($tableName, $oldColumnName, $columnData['new_name'], $columnData['definition']);
        }
    }
}

$installer->endSetup();

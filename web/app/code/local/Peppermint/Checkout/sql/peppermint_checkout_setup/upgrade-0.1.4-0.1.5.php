<?php
/**
 * @category     Peppermint
 * @package      Peppermint_Checkout
 * @author       Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();

$tableNames = [
    $installer->getTable('rockar_checkout/quote_additional_employment'),
    $installer->getTable('rockar_checkout/order_additional_employment')
];

$columnName = 'employment_status';
foreach ($tableNames as $tableName) {
    if ($connection->tableColumnExists($tableName, $columnName)) {
        $connection->modifyColumn(
            $tableName,
            $columnName,
            [
                'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length' => 5
            ]
        );
    }
}

$newColumns = [
    'employment_industry' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 5,
        'comment' => 'Employment Industry'
    ],
    'duration_at_current_employer' => [
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'comment' => 'Total Duration at Previous Employer (Months)'
    ],
    'duration_at_previous_employer' => [
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'comment' => 'Total Duration at Previous Employer (Months)'
    ],
    'previous_employer' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 100,
        'comment' => 'Previous Employer'
    ],
    'influential' => [
        'type' => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'comment' => 'Influential Person Status'
    ]
];

foreach ($tableNames as $tableName) {
    foreach ($newColumns as $column => $dataType) {
        if (!$connection->tableColumnExists($tableName, $column)) {
            $connection->addColumn($tableName, $column, $dataType);
        }
    }
}

$installer->endSetup();

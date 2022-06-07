<?php
/**
 * @category  Peppermint
 * @package   Peppermint\Checkout
 * @author    Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

/** @var Mage_Core_Model_Resource_Setup $this */
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();
$tableNames = [
    $installer->getTable('rockar_checkout/quote_additional'),
    $installer->getTable('rockar_checkout/order_additional')
];
$columnsToAdd = [
    'marital_status' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 5,
        'comment' => 'Marital Status'
    ],
    'spouse_cell_number' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 20,
        'comment' => 'Spouse Cell Number'
    ]
];

foreach ($tableNames as $tableName) {
    foreach ($columnsToAdd as $column => $dataType) {
        if ($connection->tableColumnExists($tableName, $column)) {
            $connection->modifyColumn($tableName, $column, $dataType);
        }
    }
}

$installer->endSetup();

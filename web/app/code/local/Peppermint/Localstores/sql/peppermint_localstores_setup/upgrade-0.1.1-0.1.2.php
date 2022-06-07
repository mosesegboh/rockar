<?php
/**
 * @category  Rockar
 * @package   Rockar_Localstores
 * @author    Adrian Grigorita <adrian.grigorita@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();
$tableName = $installer->getTable('peppermint_localstores/distances');
$storesTable = $installer->getTable('rockar_localstores/stores');
$uniqueColumnNames = [
    'from_store_id',
    'to_store_id'
];

if ($connection->isTableExists($tableName)) {
    $connection->dropTable($tableName);
}

$distancesTable = $connection->newTable($tableName)
    ->addColumn(
        'id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        [
            'identity' => true,
            'nullable' => false,
            'unsigned' => true,
            'primary' => true
        ],
        'ID'
    )->addColumn(
        'from_store_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        [
            'nullable' => false,
            'unsigned' => true
        ],
        'From store id'
    )->addColumn(
        'to_store_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        [
            'nullable' => false,
            'unsigned' => true
        ],
        'To store id'
    )->addColumn(
        'distance',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        255,
        [
            'nullable' => false
        ],
        'Distance'
    )->addIndex(
        $installer->getIdxName($tableName, $uniqueColumnNames),
        $uniqueColumnNames,
        ['type' => 'unique']
    )->addForeignKey(
        $installer->getFkName($storesTable, 'entity_id', $tableName, 'from_store_id'),
        'from_store_id',
        $storesTable,
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE
    );
$connection->createTable($distancesTable);

$installer->endSetup();

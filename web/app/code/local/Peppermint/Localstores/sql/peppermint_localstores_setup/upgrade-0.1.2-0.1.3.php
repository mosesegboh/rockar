<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Localstores
 * @author    Adrian Grigorita <adrian.grigorita@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();
$distancesTable = $installer->getTable('peppermint_localstores/distances');
$storesTable = $installer->getTable('rockar_localstores/stores');
$uniqueColumnNames = [
    'from_store_id',
    'to_store_id'
];

if ($connection->isTableExists($distancesTable)) {
    $connection->dropTable($distancesTable);
}

$distancesTableConnection = $connection->newTable($distancesTable)
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
        $installer->getIdxName($distancesTable, $uniqueColumnNames),
        $uniqueColumnNames,
        ['type' => 'unique']
    )->addForeignKey(
        $installer->getFkName($storesTable, 'entity_id', $distancesTable, 'from_store_id'),
        'from_store_id',
        $storesTable,
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )->addForeignKey(
        $installer->getFkName($storesTable, 'entity_id', $distancesTable, 'to_store_id'),
        'to_store_id',
        $storesTable,
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE
    );
$connection->createTable($distancesTableConnection);

$installer->endSetup();

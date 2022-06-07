<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Orderstatus
 * @author    Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();

$rockarStatusTableName = $installer->getTable('rockar_orderstatus/status');
$columnName = 'order_status';

if ($connection->tableColumnExists($rockarStatusTableName, $columnName)) {
    $connection->dropColumn(
        $rockarStatusTableName,
        $columnName
    );
}

$mappingTableName = $installer->getTable('peppermint_orderstatus/status_mapping');
$salesStatusTableName = $installer->getTable('sales/order_status');
$uniqueColumnNames = [
    'orderstatus_id',
    'order_status'
];

if ($connection->isTableExists($mappingTableName)) {
    $connection->dropTable($mappingTableName);
}

$mappingTable = $connection->newTable($mappingTableName)
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
        'Primary Key Id'
    )
    ->addColumn(
        'orderstatus_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        11,
        [
            'unsigned' => true,
            'nullable' => false
        ],
        'Customer facing order status id'
    )
    ->addColumn(
        'order_status',
        Varien_Db_Ddl_Table::TYPE_TEXT,
        32,
        ['nullable' => false],
        'Order status'
    )
    ->addIndex(
        $installer->getIdxName($mappingTableName, $uniqueColumnNames),
        $uniqueColumnNames,
        ['type' => 'unique']
    )
    ->addForeignKey(
        $installer->getFkName(
            $mappingTableName,
            'order_status',
            $salesStatusTableName,
            'status'
        ),
        'order_status',
        $salesStatusTableName,
        'status',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName(
            $mappingTableName,
            'orderstatus_id',
            $rockarStatusTableName,
            'entity_id'
        ),
        'orderstatus_id',
        $rockarStatusTableName,
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    );

$connection->createTable($mappingTable);

$installer->endSetup();

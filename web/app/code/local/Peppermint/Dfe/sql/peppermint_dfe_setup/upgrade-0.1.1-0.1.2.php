<?php

/**
 * Peppermint DFE create table reference codes
 */
/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();

$tableName = $installer->getTable('peppermint_dfe/reference_code');
if ($connection->isTableExists($tableName)) {
    $connection->dropTable($tableName);
}
$uniqueColumnNames = ['category', 'code'];

$refCodeTable = $connection->newTable($tableName)
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
        'category',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        255,
        [
            'nullable' => false
        ],
        'Category'
    )->addColumn(
        'code',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        255,
        [
            'nullable' => false
        ],
        'Reference code'
    )->addColumn(
        'description',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        255,
        [
            'nullable' => false
        ],
        'Description'
    )->addColumn(
        'is_deleted',
        Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        1,
        [
            'nullable' => false,
            'default' => 0
        ],
        'Is deleted'
    )->addIndex(
        $installer->getIdxName($tableName, $uniqueColumnNames),
        $uniqueColumnNames,
        ['type' => 'unique']
    );
$connection->createTable($refCodeTable);

$installer->endSetup();

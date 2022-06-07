<?php

/**
 * Peppermint DFE create table reference codes
 */
/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();

$tableName = 'peppermint_partexchange_customer_session';
if ($connection->isTableExists($tableName)) {
    $connection->dropTable($tableName);
}
$uniqueColumnName = ['session_id'];

$customerSessionTable = $connection->newTable($tableName)
    ->addColumn(
        'session_id',
        Varien_Db_Ddl_Table::TYPE_TEXT,
        255,
        [
            'primary'   => true,
            'nullable' => false,
        ],
        'session_id'
    )->addColumn(
        'session_data',
        Varien_Db_Ddl_Table::TYPE_TEXT,
        '64k',
        [
            'nullable' => true
        ],
        'session_data'
    )
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, ['nullable' => true])
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, ['nullable' => true])
    ->addIndex(
        $installer->getIdxName($tableName, $uniqueColumnName),
        $uniqueColumnName
    );
$connection->createTable($customerSessionTable);

$installer->endSetup();

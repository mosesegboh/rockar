<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Dfe
 * @author    Sergejs Plisko <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

/**
 * Peppermint DFE create table bank branches
 */
/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();

$tableName = $installer->getTable('peppermint_dfe/bank_branches');

if ($connection->isTableExists($tableName)) {
    $connection->dropTable($tableName);
}

$uniqueColumnNames = ['branch_code'];
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
        'bank_name',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        255,
        ['nullable' => false],
        'Bank Name'
    )->addColumn(
        'bank_code',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        255,
        ['nullable' => false],
        'Bank Code'
    )->addColumn(
        'branch_code',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        255,
        ['nullable' => false],
        'Branch Code'
    )->addColumn(
        'branch_name',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        255,
        ['nullable' => false],
        'Branch Name'
    )->addIndex(
        $installer->getIdxName($tableName, $uniqueColumnNames),
        $uniqueColumnNames,
        ['type' => 'unique']
    );
$connection->createTable($refCodeTable);

$installer->endSetup();

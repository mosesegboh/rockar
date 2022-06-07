<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Admin
 * @author    Ana-Maria Buliga <anamaria.buliga@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();
$table = $installer->getTable('peppermint_admin/role');

if ($connection->isTableExists($table)) {
    $connection->dropTable($table);
}

$connection->createTable(
    $connection->newTable($table)
        ->addColumn(
            'id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
            'S-Gate Access ID'
        )
        ->addColumn(
            'role',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            255,
            [
                'unsigned' => true,
                'nullable' => false
            ],
            'Role'
        )
        ->addColumn(
            'client_id',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            255,
            [
                'unsigned' => true,
                'nullable' => false
            ],
            'Client ID'
        )
        ->addColumn(
            'client_secret',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            255,
            [
                'unsigned' => true,
                'nullable' => false
            ],
            'Client Secret'
        )
        ->addColumn(
            'realm',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            255,
            [
                'unsigned' => true,
                'nullable' => false
            ],
            'Realm Path'
        )
        ->addColumn(
            'status',
            Varien_Db_Ddl_Table::TYPE_SMALLINT,
            null,
            [
                'unsigned' => true,
                'nullable' => false
            ],
            'Role Status'
        )
        ->addIndex(
            $installer->getIdxName(
                $table,
                ['role'],
                Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
            ),
            ['role'],
            ['type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE]
        )
);

$installer->endSetup();

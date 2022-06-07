<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Sykander Gul <sykander.gul@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();
$tableName = $installer->getTable('peppermint_sales/order_document');

if (!$connection->isTableExists($tableName)) {
    $table = $connection->newTable($tableName)
        ->addColumn(
            'id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true,
            ],
            'ID'
        )
        ->addColumn(
            'order_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            [
                'nullable' => true
            ],
            'Order ID'
        )
        ->addColumn(
            'name',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            null,
            [
                'nullable'  => true
            ],
            'Document Name'
        )
        // Helper to use to get the file
        ->addColumn(
            'file_helper',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            null,
            [
                'nullable'  => false
            ],
            'File Helper'
        )
        // Param to pass to the helper to get the file
        ->addColumn(
            'file_param',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            null,
            [
                'nullable'  => true
            ],
            'File Param'
        )
        ->addColumn(
            'created_at',
            Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
            null,
            [
                'nullable' => false,
                'default'  => Varien_Db_Ddl_Table::TIMESTAMP_INIT
            ],
            'Created At'
        )
        ->addColumn(
            'updated_at',
            Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
            null,
            [
                'nullable'  => false,
                'default'  => Varien_Db_Ddl_Table::TIMESTAMP_INIT
            ],
            'Updated At'
        );

    $connection->createTable($table);
}

$installer->endSetup();

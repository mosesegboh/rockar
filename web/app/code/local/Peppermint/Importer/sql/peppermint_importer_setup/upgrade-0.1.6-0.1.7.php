<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Importer
 * @author    Jiraphong witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();
$table = $installer->getTable('peppermint_importer/product_fail');

if (!$connection->isTableExists($table)) {
    $productFailTable = $connection->newTable($table)
        ->addColumn(
            'vin',
            Varien_Db_Ddl_Table::TYPE_VARCHAR,
            255,
            [
                'auto_increment' => false,
                'nullable' => false,
                'unique' => true,
                'primary' => true
            ],
            'Vin number'
        )->addColumn(
            'product_data',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            null,
            [
                'nullable' => false,
                'identity' => false,
            ],
            'Product Json Data'
        )->addColumn(
            'retry_count',
            Varien_Db_Ddl_Table::TYPE_SMALLINT,
            null,
            [
                'nullable' => false,
                'default' => 0,
                'comment' => 'Retry Failure Count'
            ]
        )->addColumn(
            'created_at',
            Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
            null,
            [
                'nullable' => false,
                'default'  => Varien_Db_Ddl_Table::TIMESTAMP_INIT
            ],
            'Created At'
        )->addColumn(
            'updated_at',
            Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
            null,
            [
                'nullable'  => false,
                'default'  => Varien_Db_Ddl_Table::TIMESTAMP_INIT
            ],
            'Updated At'
        );

    $connection->createTable($productFailTable);
}

$installer->endSetup();

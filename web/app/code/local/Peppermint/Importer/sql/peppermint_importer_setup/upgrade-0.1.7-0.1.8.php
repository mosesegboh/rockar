<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Importer
 * @author    Taras Kapushchak <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();
$table = $installer->getTable('peppermint_importer/accessories_missing');

if (!$connection->isTableExists($table)) {
    $table = $connection
        ->newTable($table)
        ->addColumn(
            'id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'nullable' => false,
                'unsigned' => true,
                'primary' => true
            ])
        ->addColumn(
            'product_sku',
            Varien_Db_Ddl_Table::TYPE_VARCHAR,
            64,
            [
                'nullable' => false,
                'default' => ''
            ])
        ->addColumn(
            'accessory_identifier',
            Varien_Db_Ddl_Table::TYPE_VARCHAR,
            255,
            [
                'nullable' => false
            ]);

    $connection->createTable($table);
}

$installer->endSetup();

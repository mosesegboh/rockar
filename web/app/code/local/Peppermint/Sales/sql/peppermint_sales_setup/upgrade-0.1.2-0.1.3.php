<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Taras Kapushchak <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();
$table = $installer->getTable('peppermint_sales/additional_data');

if (!$connection->isTableExists($table)) {
    $distancesTableConnection = $connection->newTable($table)
        ->addColumn(
            'vin',
            Varien_Db_Ddl_Table::TYPE_VARCHAR,
            128,
            [
                'auto_increment' => false,
                'nullable' => false,
                'unique' => true,
                'primary' => true
            ],
            'VIN number'
        )->addColumn(
            'rfs_date',
            Varien_Db_Ddl_Table::TYPE_DATE,
            null,
            [
                'identity' => false,
                'nullable' => true
            ],
            'Ready for Sale Date'
        );
    $connection->createTable($distancesTableConnection);
}

$installer->endSetup();

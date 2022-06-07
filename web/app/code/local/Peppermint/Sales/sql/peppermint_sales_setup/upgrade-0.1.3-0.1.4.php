<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Jiraphong witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();
$table = $installer->getTable('peppermint_sales/sap_fail');

if (!$connection->isTableExists($table)) {
    $sapFailTable = $connection->newTable($table)
        ->addColumn(
            'order_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            [
                'auto_increment' => false,
                'nullable' => false,
                'unique' => true,
                'primary' => true
            ],
            'Order Id'
        );
    $connection->createTable($sapFailTable);
}

$installer->endSetup();

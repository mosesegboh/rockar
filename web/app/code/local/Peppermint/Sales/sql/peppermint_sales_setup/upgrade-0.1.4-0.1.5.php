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

if ($connection->isTableExists($table)) {
    $connection->addColumn(
        $table,
        'count',
        [
            'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'length' => null,
            'nullable' => false,
            'default' => 0,
            'comment' => 'Retry Failure Count'
        ]
    );
}

$installer->endSetup();

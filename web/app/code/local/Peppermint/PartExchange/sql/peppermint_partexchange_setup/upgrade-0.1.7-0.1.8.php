<?php
/**
 * @category  Peppermint
 * @package   Peppermint_PartExchange
 * @author    Taras Kapushchak <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

/** @var $connection Varien_Db_Adapter_Interface*/
$connection = $installer->getConnection();
$installer->startSetup();

$pxTables = [
    $installer->getTable('rockar_partexchange/partExchange'),
    $installer->getTable('rockar_partexchange/order')
];
$column = 'outstanding_finance';

foreach ($pxTables as $table) {
    // Modify column if both table and column exist
    if ($connection->isTableExists($table) && $connection->tableColumnExists($table, $column)) {
        $connection->modifyColumn(
            $table,
            $column,
            [
                'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'length' => '(10,2)',
                'default' => 0,
                'nullable' => false
            ]
        );
    }
}

$installer->endSetup();

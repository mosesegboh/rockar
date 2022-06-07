<?php
/**
 * @category     Peppermint
 * @package      Peppermint_PartExchange
 * @author       Jez Horton <jez.horton@rockar.com>
 * @copyright    Copyright (c) 2020 Rockar, Ltd (https://rockar.com)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
/** @var $connection Varien_Db_Adapter_Interface*/
$connection = $installer->getConnection();
$installer->startSetup();
$orderTableName = $installer->getTable('rockar_partexchange/order');
$column = 'part_exchange_value';

// Modify column if both table and column exist
if ($connection->isTableExists($orderTableName) && $connection->tableColumnExists($orderTableName, $column)) {
    $connection->modifyColumn(
        $orderTableName,
        $column,
        [
            'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'length' => '(15,2)',
            'default' => 0,
            'nullable' => false
        ]
    );
}

$installer->endSetup();

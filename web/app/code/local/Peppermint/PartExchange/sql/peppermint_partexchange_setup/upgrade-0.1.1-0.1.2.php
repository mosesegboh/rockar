<?php
/**
 * @category     Peppermint
 * @package      Peppermint_PartExchange
 * @author       Artjoms Jermaks <techteam@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (https://rockar.com)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
/** @var $connection Varien_Db_Adapter_Interface*/
$connection = $installer->getConnection();
$installer->startSetup();
$orderTableName = $installer->getTable('rockar_partexchange/order');
$column = 'outstanding_finance_settlement';

// Add column if not exists
if (!$connection->tableColumnExists($orderTableName, $column)) {
    $connection->addColumn($orderTableName, $column, [
        'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'nullable' => true,
        'default'   => NULL,
        'comment'  => 'Selected outstanding finance settlement option',
        'after' => 'outstanding_finance'
    ]);
}

$installer->endSetup();

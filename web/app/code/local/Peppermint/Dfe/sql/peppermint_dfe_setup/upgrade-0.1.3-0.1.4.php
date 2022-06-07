<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Dfe
 * @author    Robert Ionas <robert.ionas@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();

$tableName = $installer->getTable('peppermint_dfe/resend_order');
$orderTableName = $installer->getTable('sales/order');

if ($connection->isTableExists($tableName)) {
    $connection->dropTable($tableName);
}

$uniqueColumnNames = ['order_id'];
$refCodeTable = $connection->newTable($tableName)
    ->addColumn(
        'id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        [
            'identity' => true,
            'nullable' => false,
            'unsigned' => true,
            'primary' => true
        ],
        'ID'
    )->addColumn(
        'order_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        [
            'nullable' => false
        ],
        'Order Id to resend'
    )->addColumn(
        'error_count',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        [
            'nullable' => false,
            'default' => 1
        ],
        'Count of orders that fails to resend'
    )->addForeignKey(
        $installer->getFkName($tableName, 'order_id', $orderTableName, 'entity_id'),
        'order_id',
        $orderTableName,
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE
    );
$connection->createTable($refCodeTable);

$installer->endSetup();

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Orderstatus
 * @author    Tiberiu Barkoczi <tiberiu.barkoczi@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();
$tableName = $installer->getTable('rockar_orderstatus/status');

$oldColumn = 'code';
$newColumn = 'order_status';
if ($connection->tableColumnExists($tableName, $oldColumn)) {
    $connection->changeColumn(
        $tableName,
        $oldColumn,
        $newColumn,
        [
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => 100,
            'comment' => 'Order Status'
        ]
    );
    $connection->addForeignKey(
        $installer->getFkName(
            'rockar_orderstatus/status',
            $newColumn,
            'sales/order_status',
            'status'
        ),
        $tableName,
        $newColumn,
        $installer->getTable('sales/order_status'),
        'status',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    );
}
$installer->endSetup();

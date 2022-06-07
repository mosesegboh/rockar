<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Reports
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$pricingSnapshotTable = $installer->getTable('peppermint_reports/vin_product_pricing_snapshot');

$connection = $installer->getConnection();
$installer->startSetup();

/**
 * Create table 'peppermint_vin_product_pricing_log'
 */
$table = $connection->newTable($pricingSnapshotTable)
    ->addColumn(
        'id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        [
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'auto_increment' => true,
            'primary' => true
        ]
    )
    ->addColumn(
        'product_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        ['unsigned' => true]
    )
    ->addColumn('vin_number', Varien_Db_Ddl_Table::TYPE_TEXT, 255)
    ->addColumn('final_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4')
    ->addColumn(
        'customer_group_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        ['unsigned' => true]
    )
    ->addIndex(
        $installer->getIdxName($pricingSnapshotTable, ['product_id', 'customer_group_id']),
        ['product_id', 'customer_group_id'],
        ['type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE]
    )
    ->addIndex(
        $installer->getIdxName($pricingSnapshotTable, ['vin_number', 'customer_group_id']),
        ['vin_number', 'customer_group_id'],
        ['type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE]
    )
    ->addForeignKey(
        $installer->getFkName(
            $pricingSnapshotTable,
            'customer_group_id',
            'customer/customer_group',
            'customer_group_id'
        ),
        'customer_group_id',
        $installer->getTable('customer/customer_group'),
        'customer_group_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('VIN Product Pricing Snapshot');

$connection->createTable($table);

$installer->endSetup();

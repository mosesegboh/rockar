<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Reports
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$vinProductPricingTable = $installer->getTable('peppermint_reports/vin_product_pricing_log');

$connection = $installer->getConnection();
$installer->startSetup();

/**
 * Create table 'peppermint_vin_product_pricing_log'
 */
$table = $connection->newTable($vinProductPricingTable)
    ->addColumn(
        'id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        [
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true
        ]
    )
    ->addColumn(
        'created_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        [
            'nullable' => false,
            'default' => Varien_Db_Ddl_Table::TIMESTAMP_INIT
        ]
    )
    ->addColumn(
        'product_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        ['unsigned'  => true]
    )
    ->addColumn('vin_number', Varien_Db_Ddl_Table::TYPE_TEXT, 255)
    ->addColumn('action', Varien_Db_Ddl_Table::TYPE_TEXT, 255)
    ->addColumn('price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4')
    ->addColumn('mplan_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4')
    ->addColumn('co2_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4')
    ->addColumn('final_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4')
    ->addColumn('price_rules', Varien_Db_Ddl_Table::TYPE_TEXT)
    ->addColumn(
        'customer_group_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        ['unsigned'  => true]
    )
    ->addColumn('customer_group', Varien_Db_Ddl_Table::TYPE_TEXT)
    ->addColumn('published_to_ds_date', Varien_Db_Ddl_Table::TYPE_DATE)
    ->addColumn('vehicle_condition', Varien_Db_Ddl_Table::TYPE_TEXT, 30)
    ->addColumn('cap_code', Varien_Db_Ddl_Table::TYPE_TEXT, 255)
    ->setComment('VIN Product Pricing Logs');

$connection->createTable($table);
$installer->endSetup();

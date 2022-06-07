<?php
/**
 * @category  Peppermint
 * @package   Peppermint_CatalogRule
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();

$logsTable = $installer->getTable('peppermint_catalogrule/rules_log');

if ($connection->isTableExists($logsTable)) {
    $connection->dropTable($logsTable);
}

/**
 * Create table 'peppermint_rules_log'
 */
$table = $connection->newTable($logsTable)
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
    ->addColumn('username', Varien_Db_Ddl_Table::TYPE_TEXT)
    ->addColumn('full_name', Varien_Db_Ddl_Table::TYPE_TEXT)
    ->addColumn('action_type', Varien_Db_Ddl_Table::TYPE_TEXT)
    ->addColumn('rule_type', Varien_Db_Ddl_Table::TYPE_TEXT)
    ->addColumn('rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, ['unsigned' => true])
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT)
    ->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_SMALLINT)
    ->addColumn('website_ids', Varien_Db_Ddl_Table::TYPE_TEXT)
    ->addColumn('from_date', Varien_Db_Ddl_Table::TYPE_DATE)
    ->addColumn('to_date', Varien_Db_Ddl_Table::TYPE_DATE)
    ->addColumn('sort_order', Varien_Db_Ddl_Table::TYPE_INTEGER)
    ->addColumn('customer_group_ids', Varien_Db_Ddl_Table::TYPE_TEXT)
    ->addColumn('conditions_serialized', Varien_Db_Ddl_Table::TYPE_TEXT)
    ->addColumn('actions_serialized', Varien_Db_Ddl_Table::TYPE_TEXT)
    ->addColumn('simple_action', Varien_Db_Ddl_Table::TYPE_TEXT)
    ->addColumn('simple_action', Varien_Db_Ddl_Table::TYPE_TEXT)
    ->addColumn('discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4')
    ->addColumn('stop_rules_processing', Varien_Db_Ddl_Table::TYPE_SMALLINT)
    ->addColumn('coupon_code', Varien_Db_Ddl_Table::TYPE_TEXT)
    ->addColumn('uses_per_coupon', Varien_Db_Ddl_Table::TYPE_INTEGER, null, ['unsigned' => true])
    ->addColumn('uses_per_customer', Varien_Db_Ddl_Table::TYPE_INTEGER, null, ['unsigned' => true])
    ->addColumn('is_rss', Varien_Db_Ddl_Table::TYPE_SMALLINT)
    ->setComment('Rules log table');

$connection->createTable($table);

$installer->endSetup();

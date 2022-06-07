<?php
/**
 * @category  Peppermint
 * @package   Peppermint_CatalogRule
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

/* @var $installer Mage_Core_Model_Resource_Setup */

$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();

/**
 * Create table 'catalogrule/rule'
 */
$table = $connection
    ->newTable($installer->getTable('peppermint_catalogrule/catalogrule_pending'))
    ->addColumn('rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true
    ])
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255)
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT)
    ->addColumn('from_date', Varien_Db_Ddl_Table::TYPE_DATE)
    ->addColumn('to_date', Varien_Db_Ddl_Table::TYPE_DATE)
    ->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, [
        'nullable'  => false,
        'default'   => '0'
    ])
    ->addColumn('conditions_serialized', Varien_Db_Ddl_Table::TYPE_TEXT, '2M')
    ->addColumn('actions_serialized', Varien_Db_Ddl_Table::TYPE_TEXT, '2M')
    ->addColumn('stop_rules_processing', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, [
        'nullable'  => false,
        'default'   => '1'
    ])
    ->addColumn('sort_order', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0'
    ])
    ->addColumn('simple_action', Varien_Db_Ddl_Table::TYPE_TEXT, 32, [
    ])
    ->addColumn('discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, [12,4], [
        'nullable'  => false,
        'default'   => 0.0000
    ])
    ->addColumn('sub_is_enable', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, [
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0'
    ])
    ->addColumn('sub_simple_action', Varien_Db_Ddl_Table::TYPE_TEXT, 32)
    ->addColumn('sub_discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, [12,4], [
        'nullable'  => false,
        'default'   => 0.0000
    ])
    ->addColumn('customer_group_ids', Varien_Db_Ddl_Table::TYPE_TEXT, '64k')
    ->addColumn('website_ids', Varien_Db_Ddl_Table::TYPE_TEXT, '64k')
    ->addColumn('is_approved', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, [
        'nullable'  => false,
        'default'   => '0'
    ])
    ->addColumn('pending_action', Varien_Db_Ddl_Table::TYPE_TEXT, 255);

$installer->getConnection()->createTable($table);
$installer->endSetup();
<?php
/**
 * @category  Peppermint
 * @package   Peppermint_SalesRule
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar Ltd (http://rockar.com)
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Create table 'peppermint_salesrule/rule_pending'
 */
$pendingRuleTable = $installer->getTable('peppermint_salesrule/rule_pending');
$connection = $installer->getConnection();

if (!$connection->isTableExists($pendingRuleTable)) {
    $table = $connection
        ->newTable($pendingRuleTable)
        ->addColumn('rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ], 'Rule Id')
        ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, [
        ], 'Name')
        ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', [
        ], 'Description')
        ->addColumn('from_date', Varien_Db_Ddl_Table::TYPE_DATE, null, [
        ], 'From Date')
        ->addColumn('to_date', Varien_Db_Ddl_Table::TYPE_DATE, null, [
        ], 'To Date')
        ->addColumn('uses_per_customer', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
            'nullable' => false,
            'default' => '0',
        ], 'Uses Per Customer')
        ->addColumn('customer_group_ids', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', [
        ], 'Customer Group Ids')
        ->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, [
            'nullable' => false,
            'default' => '0',
        ], 'Is Active')
        ->addColumn('conditions_serialized', Varien_Db_Ddl_Table::TYPE_TEXT, '2M', [
        ], 'Conditions Serialized')
        ->addColumn('actions_serialized', Varien_Db_Ddl_Table::TYPE_TEXT, '2M', [
        ], 'Actions Serialized')
        ->addColumn('stop_rules_processing', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, [
            'nullable' => false,
            'default' => '1',
        ], 'Stop Rules Processing')
        ->addColumn('is_advanced', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, [
            'unsigned' => true,
            'nullable' => false,
            'default' => '1',
        ], 'Is Advanced')
        ->addColumn('product_ids', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', [
        ], 'Product Ids')
        ->addColumn('sort_order', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
            'unsigned' => true,
            'nullable' => false,
            'default' => '0',
        ], 'Sort Order')
        ->addColumn('simple_action', Varien_Db_Ddl_Table::TYPE_TEXT, 32, [
        ], 'Simple Action')
        ->addColumn('discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, [12, 4], [
            'nullable' => false,
            'default' => '0.0000',
        ], 'Discount Amount')
        ->addColumn('discount_qty', Varien_Db_Ddl_Table::TYPE_DECIMAL, [12, 4], [
        ], 'Discount Qty')
        ->addColumn('discount_step', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
            'unsigned' => true,
            'nullable' => false,
        ], 'Discount Step')
        ->addColumn('simple_free_shipping', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, [
            'unsigned' => true,
            'nullable' => false,
            'default' => '0',
        ], 'Simple Free Shipping')
        ->addColumn('apply_to_shipping', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, [
            'unsigned' => true,
            'nullable' => false,
            'default' => '0',
        ], 'Apply To Shipping')
        ->addColumn('times_used', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
            'unsigned' => true,
            'nullable' => false,
            'default' => '0',
        ], 'Times Used')
        ->addColumn('is_rss', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, [
            'nullable' => false,
            'default' => '0',
        ], 'Is Rss')
        ->addColumn('coupon_type', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, [
            'unsigned' => true,
            'nullable' => false,
            'default' => '1',
        ], 'Coupon Type')
        ->addColumn('use_auto_generation', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, [
            'nullable' => false,
            'default' => '0'
        ], 'Use Auto Generation')
        ->addColumn('uses_per_coupon', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
            'nullable' => false,
            'default' => '0'
        ], 'Uses Per Coupon')
        ->addColumn('order_amend_honor_value', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, [
            'nullable' => true,
            'default' => 0,
        ], 'Honor coupon value in order amend')
        ->addColumn('website_ids', Varien_Db_Ddl_Table::TYPE_TEXT, 255, [
        ], 'Website Ids')
        ->addColumn('customer_group_ids', Varien_Db_Ddl_Table::TYPE_TEXT, 255, [
        ], 'Customer Group Ids')
        ->addColumn('store_labels', Varien_Db_Ddl_Table::TYPE_TEXT, 4000, [
        ], 'Store labels')
        ->addColumn('is_approved', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, [
            'nullable' => false,
            'default' => '0',
        ], 'Is Approved')
        ->addColumn('pending_action', Varien_Db_Ddl_Table::TYPE_TEXT, 255, [
        ], 'Pending Action')
        ->addIndex($installer->getIdxName('peppermint_salesrule/rule_pending', ['is_active', 'sort_order', 'to_date', 'from_date']),
            ['is_active', 'sort_order', 'to_date', 'from_date'])
        ->setComment('Pending Salesrule');

    $connection->createTable($table);
}

/**
 * Create table 'peppermint_salesrule/coupon_pending'
 */
$couponPendingTable = $installer->getTable('peppermint_salesrule/coupon_pending');

if (!$connection->isTableExists($couponPendingTable)) {
    $table = $connection
        ->newTable($couponPendingTable)
        ->addColumn('coupon_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ], 'Coupon Id')
        ->addColumn('rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
            'unsigned' => true,
            'nullable' => false,
        ], 'Rule Id')
        ->addColumn('code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, [
        ], 'Code')
        ->addColumn('usage_limit', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
            'unsigned' => true,
        ], 'Usage Limit')
        ->addColumn('usage_per_customer', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
            'unsigned' => true,
        ], 'Usage Per Customer')
        ->addColumn('times_used', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
            'unsigned' => true,
            'nullable' => false,
            'default' => '0',
        ], 'Times Used')
        ->addColumn('expiration_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, [
        ], 'Expiration Date')
        ->addColumn('is_primary', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, [
            'unsigned' => true,
        ], 'Is Primary')
        ->addColumn(
            'created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null,
            [
                'nullable' => false,
                'default' => Varien_Db_Ddl_Table::TIMESTAMP_INIT
            ], 'Coupon Code Creation Date'
        )
        ->addColumn(
            'type', Varien_Db_Ddl_Table::TYPE_SMALLINT, null,
            [
                'nullable' => false,
                'default' => 0
            ],
            'Coupon Code Type'
        )
        ->addIndex($installer->getIdxName('peppermint_salesrule/coupon_pending', ['code'], Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
            ['code'], ['type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE])
        ->addIndex($installer->getIdxName('peppermint_salesrule/coupon_pending', ['rule_id', 'is_primary'], Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
            ['rule_id', 'is_primary'], ['type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE])
        ->addIndex($installer->getIdxName('peppermint_salesrule/coupon_pending', ['rule_id']),
            ['rule_id'])
        ->addForeignKey($installer->getFkName('peppermint_salesrule/coupon_pending', 'rule_id', 'peppermint_salesrule/rule_pending', 'rule_id'),
            'rule_id', $pendingRuleTable, 'rule_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
        ->setComment('Salesrule Coupon');

    $connection->createTable($table);
}

$installer->endSetup();

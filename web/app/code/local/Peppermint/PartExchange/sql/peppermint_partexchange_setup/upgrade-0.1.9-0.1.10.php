<?php
/**
 * @category Peppermint
 * @package Peppermint_PartExchange
 * @author Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
/** @var $connection Varien_Db_Adapter_Interface */
$connection = $installer->getConnection();
$installer->startSetup();
$table = Peppermint_PartExchange_Model_Resource_Promotions_Rule::PARTEXCHANGE_PROMOTIONRULE_PRODUCT_TABLE;

/**
 * Create table 'peppermint_partexchange_promotionrule_product'
 */
$table = $connection->newTable($table)
    ->addColumn('rule_product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true
    ], 'Rule Product Id')
    ->addColumn('rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'unsigned' => true,
        'nullable' => false,
        'default' => '0'
    ], 'Rule Id')
    ->addColumn('from_time', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'unsigned' => true,
        'nullable' => false,
        'default' => '0'
    ], 'From Time')
    ->addColumn('to_time', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'unsigned' => true,
        'nullable' => false,
        'default' => '0'
    ], 'To time')
    ->addColumn('customer_group_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, [
        'unsigned' => true,
        'nullable' => false,
        'default' => '0'
    ], 'Customer Group Id')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'unsigned' => true,
        'nullable' => false,
        'default' => '0'
    ], 'Product Id')
    ->addColumn('action_operator', Varien_Db_Ddl_Table::TYPE_TEXT, 10, [
        'default' => 'by_fixed',
    ], 'Action Operator')
    ->addColumn('action_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, [12, 4], [
        'nullable' => false,
        'default' => '0.0000'
    ], 'Action Amount')
    ->addColumn('action_stop', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, [
        'nullable' => false,
        'default' => '0'
    ], 'Action Stop')
    ->addColumn('sort_order', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'unsigned' => true,
        'nullable' => false,
        'default' => '0'
    ], 'Sort Order')
    ->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, [
        'unsigned' => true,
        'nullable' => false
    ], 'Website Id')
    ->addIndex($installer->getIdxName(
        $table,
        ['rule_id', 'from_time', 'to_time', 'website_id', 'customer_group_id', 'product_id', 'sort_order'],
        true
    ),
        ['rule_id', 'from_time', 'to_time', 'website_id', 'customer_group_id', 'product_id', 'sort_order'],
        ['type' => 'unique']
    )
    ->addIndex($installer->getIdxName($table, ['rule_id']),
        ['rule_id'])
    ->addIndex($installer->getIdxName($table, ['customer_group_id']),
        ['customer_group_id'])
    ->addIndex($installer->getIdxName($table, ['website_id']),
        ['website_id'])
    ->addIndex($installer->getIdxName($table, ['from_time']),
        ['from_time'])
    ->addIndex($installer->getIdxName($table, ['to_time']),
        ['to_time'])
    ->addIndex($installer->getIdxName($table, ['product_id']),
        ['product_id'])
    ->addForeignKey($installer->getFkName($table, 'product_id', 'catalog/product', 'entity_id'),
        'product_id', $installer->getTable('catalog/product'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName($table, 'customer_group_id', 'customer/customer_group', 'customer_group_id'),
        'customer_group_id', $installer->getTable('customer/customer_group'), 'customer_group_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName($table, 'rule_id', 'catalogrule/rule', 'rule_id'),
        'rule_id', $installer->getTable('catalogrule/rule'), 'rule_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName($table, 'website_id', 'core/website', 'website_id'),
        'website_id', $installer->getTable('core/website'), 'website_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Px Promotion Rule Product');

$connection->createTable($table);

$installer->endSetup();

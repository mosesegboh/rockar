<?php
/**
 * @category  Peppermint
 * @package   Peppermint_PartExchange
 * @author    Ausma Smite <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
/** @var $connection Varien_Db_Adapter_Interface */
$connection = $installer->getConnection();

$promotionsPendingTable = $installer->getTable('peppermint_partexchange_promotions_pending');

$installer->startSetup();

/**
 * Create table 'peppermint_partexchange_promotions_pending' if not exists
 */
if (!$connection->isTableExists($promotionsPendingTable)){
    $table = $connection->newTable($promotionsPendingTable)
        ->addColumn('rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary'  => true
        ], 'Rule Id')
        ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, [
        ], 'Name')
        ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, null, [
        ], 'Description')
        ->addColumn('scope', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, [
        ], 'Scope')
        ->addColumn('from_date', Varien_Db_Ddl_Table::TYPE_DATE, null, [
        ], 'From Date')
        ->addColumn('to_date', Varien_Db_Ddl_Table::TYPE_DATE, null, [
        ], 'To Date')
        ->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, [
            'nullable' => false,
            'default' => '0'
        ], 'Is Active')
        ->addColumn('conditions_serialized', Varien_Db_Ddl_Table::TYPE_TEXT, '2M', [
        ],'Conditions Serialized')
        ->addColumn('actions_serialized', Varien_Db_Ddl_Table::TYPE_TEXT, '2M', [
        ],'Actions Serialized')
        ->addColumn('stop_rules_processing', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, [
            'nullable' => false,
            'default' => '1'
        ], 'Stop Rules Processing')
        ->addColumn('sort_order', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
            'unsigned' => true,
            'nullable' => false,
            'default' => '0'
        ], 'Sort Order')
        ->addColumn('simple_action', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, [
        ], 'Simple Action')
        ->addColumn('discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, [12, 4], [
            'nullable'  => false,
            'default'   => 0.0000
        ], 'Discount Amount')
        ->addColumn('customer_group_ids', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', [
        ], 'Customer Group Ids')
        ->addColumn('website_ids', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', [
        ], 'Website Ids')
        ->addColumn('is_approved', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, [
            'unsigned' => true,
            'nullable' => false,
            'default' => '0'
        ], 'Is Approved')
        ->addColumn('pending_action', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, [
        ], 'Pending Action');

    $connection->createTable($table);
}

$installer->endSetup();

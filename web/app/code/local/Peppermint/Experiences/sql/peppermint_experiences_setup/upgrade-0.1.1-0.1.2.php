<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Experiences
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$rulesTable = $installer->getTable('peppermint_experiences/experiences_rules');
$websitesTable = $installer->getTable('core/website');
$customerGroupsTable = $installer->getTable('customer/customer_group');
$rulesWebsitesTable = $installer->getTable('peppermint_experiences/experiences_rules_website');
$rulesCustomerGroupsTable = $installer->getTable('peppermint_experiences/experiences_rules_customer_group');

/**
 * Create table 'peppermint_experiences/experiences_rules'
 */
$table = $installer->getConnection()
    ->newTable($rulesTable)
    ->addColumn('rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true
    ], 'Rule Id')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, [], 'Name')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', [], 'Description')
    ->addColumn('from_date', Varien_Db_Ddl_Table::TYPE_DATE, null, [], 'From Date')
    ->addColumn('to_date', Varien_Db_Ddl_Table::TYPE_DATE, null, [], 'To Date')
    ->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, [
        'nullable' => false,
        'default' => '0'
    ], 'Is Active')
    ->addColumn('priority', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'nullable' => false,
        'default' => '0'
    ], 'Priority')
    ->addColumn('experience_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [], 'Experiences Id')
    ->addColumn('conditions_serialized', Varien_Db_Ddl_Table::TYPE_TEXT, '2M', [], 'Conditions Serialized')
    ->addColumn('uses_per_customer', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'nullable' => false,
        'default' => '0'
    ], 'Uses Per Customer')
    ->addColumn('uses_per_coupon', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'nullable' => false,
        'default' => '0'
    ], 'Uses Per Coupon')
    ->addColumn('use_auto_generation', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, [
        'nullable' => false,
        'default' => '0'
    ], 'Use Auto Generation')
    ->addColumn('coupon_type', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, [
        'nullable' => false,
        'unsigned' => true,
        'default' => '1'
    ], 'Coupon Type');

$installer->getConnection()->createTable($table);

/**
 * Create table 'peppermint_experiences/rules_website''
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable($rulesWebsitesTable))
    ->addColumn('rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'unsigned' => true,
        'nullable' => false,
        'primary' => true
    ],
        'Rule Id'
    )
    ->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, [
        'unsigned' => true,
        'nullable' => false,
        'primary' => true
    ],
        'Website Id'
    )
    ->addIndex(
        $installer->getIdxName('peppermint_experiences/experiences_rules_website', ['rule_id']),
        ['rule_id']
    )
    ->addIndex(
        $installer->getIdxName('peppermint_experiences/experiences_rules_website', ['website_id']),
        ['website_id']
    )
    ->addForeignKey($installer->getFkName('peppermint_experiences/experiences_rules_website', 'rule_id', 'peppermint_experiences/experiences_rules', 'rule_id'),
        'rule_id', $rulesTable, 'rule_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey($installer->getFkName('peppermint_experiences/experiences_rules_website', 'website_id', 'core/website', 'website_id'),
        'website_id', $websitesTable, 'website_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Experiences Rules To Websites Relations');

$installer->getConnection()->createTable($table);

/**
 * Create table 'peppermint_experiences/rules_customer_group''
 */
$table = $installer->getConnection()->newTable($rulesCustomerGroupsTable)
    ->addColumn('rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'unsigned' => true,
        'nullable' => false,
        'primary' => true
    ],
        'Rule Id'
    )
    ->addColumn('customer_group_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, [
        'unsigned' => true,
        'nullable' => false,
        'primary' => true
    ],
        'Customer Group Id'
    )
    ->addIndex(
        $installer->getIdxName('peppermint_experiences/experiences_rules_customer_group', ['rule_id']),
        ['rule_id']
    )
    ->addIndex(
        $installer->getIdxName('peppermint_experiences/experiences_rules_customer_group', ['customer_group_id']),
        ['customer_group_id']
    )
    ->addForeignKey($installer->getFkName('peppermint_experiences/experiences_rules_customer_group', 'rule_id', 'peppermint_experiences/experiences_rules', 'rule_id'),
        'rule_id', $rulesTable, 'rule_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('peppermint_experiences/experiences_rules_customer_group', 'customer_group_id',
            'customer/customer_group', 'customer_group_id'
        ),
        'customer_group_id', $customerGroupsTable, 'customer_group_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Experiences Rules To Customer Groups Relations');

$installer->getConnection()->createTable($table);

$installer->endSetup();

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
$rule = $installer->getTable('peppermint_experiences/experiences_rules');
$coupon = $installer->getTable('peppermint_experiences/coupon');
$couponUsage = $installer->getTable('peppermint_experiences/coupon_usage');

/**
 * Create table 'peppermint_experiences/coupon'
 */

$couponTable = $installer->getConnection()
    ->newTable($coupon)
    ->addColumn('coupon_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true
    ], 'Coupon Id')
    ->addColumn('rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'unsigned' => true,
        'nullable' => false
    ])
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, [
        'unique' => true
    ], 'Coupon code')
    ->addColumn('usage_limit', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'unsigned' => true,
        'nullable' => false
    ])
    ->addColumn('usage_per_customer', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'unsigned' => true,
        'nullable' => false
    ])
    ->addColumn('times_used', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'unsigned' => true,
        'nullable' => false
    ])
    ->addColumn('is_primary', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, [
        'nullable' => false,
        'default' => 0
    ])
    ->addColumn('expiration_date', Varien_Db_Ddl_Table::TYPE_DATETIME, null, ['nullable' => true])
    ->addColumn(
        'type', Varien_Db_Ddl_Table::TYPE_SMALLINT, null,
        [
            'nullable' => false,
            'default' => 0
        ],
        'Coupon Code Type'
    )
    ->addForeignKey(
        $installer->getFkName(
            'peppermint_experiences/coupon',
            'rule_id',
            'peppermint_experiences/experiences_rules',
            'rule_id'
        ),
        'rule_id',
        $installer->getTable('peppermint_experiences/experiences_rules'),
        'rule_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    );

$installer->getConnection()->createTable($couponTable);

$couponUsagesTable = $installer->getConnection()
    ->newTable($couponUsage)
    ->addColumn('coupon_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'unsigned' => true,
        'nullable' => false,
        'primary' => true
    ], 'Coupon Id')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'unsigned' => true,
        'nullable' => false,
        'primary' => true
    ], 'Customer Id')
    ->addColumn('times_used', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'unsigned' => true,
        'nullable' => false,
        'default' => '0'
    ], 'Coupon Id');

$installer->getConnection()->createTable($couponUsagesTable);

$installer->endSetup();

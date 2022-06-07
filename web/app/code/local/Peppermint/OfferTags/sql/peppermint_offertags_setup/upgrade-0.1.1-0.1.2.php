<?php
/**
 * @category  Peppermint
 * @package   Peppermint_OfferTags
 * @author    Ketevani Revazishvili <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$rulesTable              = $installer->getTable('peppermint_offertags/offertag_rules');
$financeGroupTable       = $installer->getTable('rockar_financingoptions/group');
$rulesFinanceGroupTable  = $installer->getTable('peppermint_offertags/offertag_rules_finance_group');

$table = $installer->getConnection()
    ->newTable($installer->getTable($rulesFinanceGroupTable))
    ->addColumn('rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true
        ],
        'Rule Id'
    )
    ->addColumn('group_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true
        ],
        'Finance Group Id'
    )
    ->addIndex(
        $installer->getIdxName('peppermint_offertags/offertag_rules_finance_group', ['rule_id']),
        ['rule_id']
    )
    ->addIndex(
        $installer->getIdxName('peppermint_offertags/offertag_rules_finance_group', ['group_id']),
        ['group_id']
    )
    ->addForeignKey($installer->getFkName('peppermint_offertags/offertag_rules_finance_group', 'rule_id', 'peppermint_offertags/offertag_rules', 'rule_id'),
        'rule_id', $rulesTable, 'rule_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey($installer->getFkName('peppermint_offertags/offertag_rules_finance_group', 'group_id', 'rockar_financingoptions/group', 'group_id'),
        'group_id', $financeGroupTable, 'group_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Offer Tag Rules To Finance Group Relations');

$installer->getConnection()->createTable($table);

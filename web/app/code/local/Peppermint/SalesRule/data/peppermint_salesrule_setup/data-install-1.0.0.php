<?php
/**
 * @category  Peppermint
 * @package   Peppermint_SalesRule
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar Ltd (http://rockar.com)
 */

/** @var $this Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();

$select = $connection->select()
    ->from(['original' => $installer->getTable('salesrule/rule')])
    ->joinInner(
        ['websites' => $installer->getTable('salesrule/website')],
        'original.rule_id = websites.rule_id',
        ['website_ids' => 'GROUP_CONCAT(distinct websites.website_id)']
    )
    ->joinInner(
        ['customer_groups' => $installer->getTable('salesrule/customer_group')],
        'original.rule_id = customer_groups.rule_id',
        ['customer_group_ids' => 'GROUP_CONCAT(distinct customer_groups.customer_group_id)']
    )
    ->joinLeft(
        ['sl' => $installer->getTable('salesrule/label')],
        'original.rule_id = sl.rule_id',
        ['store_labels' => 'GROUP_CONCAT(sl.store_id, "=", sl.label SEPARATOR \'' . Peppermint_SalesRule_Model_RulePending::STORE_LABELS_SEPARATOR . '\')']
    )
    ->columns([
        'is_approved' => new Zend_Db_Expr ('1'),
        'pending_action' => new Zend_Db_Expr ('NULL')
    ])
    ->group('original.rule_id');

$connection->query(
    $select->insertFromSelect(
        $installer->getTable('peppermint_salesrule/rule_pending')
    )
);

$select = $installer->getConnection()
    ->select()
    ->from(['original' => $installer->getTable('salesrule/coupon')]);

$connection->query(
    $select->insertFromSelect($installer->getTable('peppermint_salesrule/coupon_pending'))
);

$installer->endSetup();

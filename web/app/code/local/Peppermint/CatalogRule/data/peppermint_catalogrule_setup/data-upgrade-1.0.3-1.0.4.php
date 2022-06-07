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

$select = $connection->select()
    ->from(['catalogrule' => $installer->getTable('catalogrule/rule')])
    ->joinInner(
        ['customer_groups' => $installer->getTable('catalogrule/customer_group')],
        'catalogrule.rule_id = customer_groups.rule_id',
        ['customer_group_ids' => 'GROUP_CONCAT(DISTINCT customer_groups.customer_group_id)']
    )
    ->joinInner(
        ['websites' => $installer->getTable('catalogrule/website')],
        'catalogrule.rule_id = websites.rule_id',
        ['website_ids' => 'GROUP_CONCAT(DISTINCT websites.website_id)']
    )
    ->columns(['is_approved' => new Zend_Db_Expr ('1'), 'pending_action' => new Zend_Db_Expr ('NULL')])
    ->group('catalogrule.rule_id');

$connection->query(
    $select->insertFromSelect(
        $installer->getTable('peppermint_catalogrule/catalogrule_pending'))
);

$installer->endSetup();

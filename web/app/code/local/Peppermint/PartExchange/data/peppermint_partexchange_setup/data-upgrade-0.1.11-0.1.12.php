<?php
/**
 * @category  Peppermint
 * @package   Peppermint_PartExchange
 * @author    Ausma Smite <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer              = $this;
/** @var $connection Varien_Db_Adapter_Interface */
$connection             = $installer->getConnection();

$promotionsTable        = $installer->getTable('rockar_partexchange_promotions');
$customerGroupsTable    = $installer->getTable('rockar_partexchange_promotions_customer_group');
$websitesTable          = $installer->getTable('rockar_partexchange_promotions_website');
$promotionsPendingTable = $installer->getTable('peppermint_partexchange_promotions_pending');

$installer->startSetup();

$select = $connection->select()
    ->from(['promotions' => $installer->getTable($promotionsTable)])
    ->joinInner(
        ['customer_groups' => $installer->getTable($customerGroupsTable)],
        'promotions.rule_id = customer_groups.rule_id',
        ['customer_group_ids' => 'GROUP_CONCAT(DISTINCT customer_groups.customer_group_id)']
    )
    ->joinInner(
        ['websites' => $installer->getTable($websitesTable)],
        'promotions.rule_id = websites.rule_id',
        ['website_ids' => 'GROUP_CONCAT(DISTINCT websites.website_id)']
    )
    ->columns(['is_approved' => new Zend_Db_Expr ('1'), 'pending_action' => new Zend_Db_Expr ('NULL')])
    ->group('promotions.rule_id');

$query = $select->insertFromSelect($promotionsPendingTable);
$connection->query($query);

$installer->endSetup();

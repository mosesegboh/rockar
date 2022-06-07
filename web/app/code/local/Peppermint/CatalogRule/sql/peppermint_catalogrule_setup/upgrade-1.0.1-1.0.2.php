<?php
/**
 * @category  Peppermint
 * @package   Peppermint_CatalogRule
 * @author    Ausma Smite <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

/** @var $this Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$orderTable = $installer->getTable('sales/order');
$installer->getConnection()
    ->addColumn($orderTable, 'pricing_rule_snapshot', [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'identity' => false,
        'nullable' => true,
        'primary' => false,
        'comment' => 'Pricing Rule Snapshot'
    ]);

$installer->endSetup();

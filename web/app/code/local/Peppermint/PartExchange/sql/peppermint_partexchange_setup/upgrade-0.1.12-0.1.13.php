<?php
/**
 * @category Peppermint
 * @package Peppermint_PartExchange
 * @author Kalvis Ostrovskis <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

/** @var $connection Varien_Db_Adapter_Interface */
$connection = $installer->getConnection();

$installer->startSetup();
$table = Peppermint_PartExchange_Model_Resource_Promotions_Rule::PARTEXCHANGE_PROMOTIONRULE_PRODUCT_TABLE;

if ($connection->isTableExists($installer->getTable($table))) {
    $connection->dropForeignKey(
        $installer->getTable($table),
        $installer->getFkName($table, 'rule_id', 'catalogrule/rule', 'rule_id')
    )->addForeignKey(
        $installer->getFkName($table, 'rule_id', 'rockar_partexchange/promotions_rule', 'rule_id'),
        $installer->getTable($table),
        'rule_id',
        $installer->getTable('rockar_partexchange/promotions_rule'),
        'rule_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    );
}

$installer->endSetup();

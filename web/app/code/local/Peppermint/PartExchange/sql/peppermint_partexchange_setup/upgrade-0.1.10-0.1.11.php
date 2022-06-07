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
$priceTable = Peppermint_PartExchange_Model_Resource_Promotions_Rule::PARTEXCHANGE_PROMOTIONRULE_PRODUCT_PRICE_TABLE;

// Add column if not exists
if (!$connection->tableColumnExists($table, 'product_price')) {
    $connection->addColumn($table, 'product_price', [
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length' => '12,4',
        'nullable' => true,
        'default' => NULL,
        'comment' => 'product base price'
    ]);

}
if (!$connection->isTableExists($priceTable)) {
    $connection->createTable(
        $connection->newTable($priceTable)
            ->addColumn('rule_product_price_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ], 'Rule Product Price Id')
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
            ->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, [
                'unsigned' => true,
                'nullable' => false
            ], 'Website Id')
            ->addColumn('action_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, [12, 4], [
                'nullable' => false,
                'default' => '0.0000'
            ], 'Calculated Shortfall Allowance Amount')
            ->addIndex(
                $installer->getIdxName(
                    $priceTable,
                    ['website_id', 'customer_group_id', 'product_id'],
                    true
                ),
                ['website_id', 'customer_group_id', 'product_id'],
                ['type' => 'unique']
            )
            ->addIndex($installer->getIdxName($priceTable, ['product_id']),
                ['product_id'])

    );
}

$installer->endSetup();

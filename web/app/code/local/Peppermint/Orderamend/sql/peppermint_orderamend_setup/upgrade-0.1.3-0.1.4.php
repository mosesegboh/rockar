<?php
/**
 * @category     Peppermint
 * @package      Peppermint_Orderamend
 * @author       Igors Zhunins <techteam@rockar.com>
 * @copyright    Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

/** @var Mage_Core_Model_Resource_Setup $this */

$orderAmendCatalogRuleTable = $this->getTable('rockar_orderamend/catalogRule_data');
$column = 'all_catalog_rule_prices';

$connection = $this->getConnection();

// Add column if not exists
if (!$connection->tableColumnExists($orderAmendCatalogRuleTable, $column)) {
    $connection->addColumn(
        $orderAmendCatalogRuleTable,
        $column,
        [
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'nullable' => true,
            'comment' => 'All catalog rule prices'
        ]
    );
}


<?php
/**
 * @category  Pepermint
 * @package   Peppermint_Orderamend
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

/** @var Mage_Core_Model_Resource_Setup $this */
$orderAmendSalesRuleTable = $this->getTable('rockar_orderamend/salesRule_data');
$column = 'honor_value';

$connection = $this->getConnection();

// Add column if not exists
if (!$connection->tableColumnExists($orderAmendSalesRuleTable, $column)) {
    $connection->addColumn($orderAmendSalesRuleTable, $column, [
        'type'     => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'default'  => 0,
        'comment'  => 'Honor coupon value'
    ]);
}

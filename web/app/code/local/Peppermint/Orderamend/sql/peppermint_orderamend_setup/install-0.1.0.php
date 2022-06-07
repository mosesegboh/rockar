<?php

/**
 * @category  Peppermint
 * @package   Peppermint_Orderamend
 * @author    Adrian Grigorita <adrian.grigorita@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$connection = $installer->getConnection();

$installer->startSetup();
$table_finance_data = [
    'rate_subvention_type' => 'VARCHAR(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL',
    'rate_subvention_value' => 'DECIMAL(9, 2) DEFAULT 0',
    'applicable_stock_type' => 'VARCHAR(10) COLLATE utf8mb4_unicode_ci',
    'maximum_amount_of_finance' => 'DECIMAL(11, 2) DEFAULT 0',
    'minimum_amount_of_finance' => 'DECIMAL(11, 2) DEFAULT 0'
];
$tableName = $installer->getTable('rockar_orderamend/finance_data');
foreach ($table_finance_data as $column => $dataType) {
    if (!$connection->tableColumnExists($tableName, $column)) {
        $connection->addColumn($tableName, $column, $dataType);
    }
}
$table_finance_terms = [
    'individual_fee_monthly' => 'DECIMAL(11, 6) DEFAULT 0',
    'individual_fee_capitalised' => 'DECIMAL(11, 6) DEFAULT 0',
    'corporate_fee_monthly' => 'DECIMAL(11, 6) DEFAULT 0',
    'corporate_fee_capitalised' => 'DECIMAL(11, 6) DEFAULT 0'
];
$tableName = $installer->getTable('rockar_orderamend/finance_terms');
foreach ($table_finance_terms as $column => $dataType) {
    if (!$connection->tableColumnExists($tableName, $column)) {
        $connection->addColumn($tableName, $column, $dataType);
    }
}

$installer->endSetup();

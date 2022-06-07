<?php

/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$connection = $installer->getConnection();

$installer->startSetup();

$tableName = $installer->getTable('rockar_financingoptions/data');

if (!$connection->tableColumnExists($tableName, 'rate_subvention_type')) {
    $connection->addColumn($tableName, 'rate_subvention_type', 'VARCHAR(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL');
}

if (!$connection->tableColumnExists($tableName, 'rate_subvention_value')) {
    $connection->addColumn($tableName, 'rate_subvention_value', 'DECIMAL(9, 2) DEFAULT 0');
}

if (!$connection->tableColumnExists($tableName, 'applicable_stock_type')) {
    $connection->addColumn($tableName, 'applicable_stock_type', 'VARCHAR(10) COLLATE utf8mb4_unicode_ci');
}

if (!$connection->tableColumnExists($tableName, 'maximum_amount_of_finance')) {
    $connection->addColumn($tableName, 'maximum_amount_of_finance', 'DECIMAL(11, 2) DEFAULT 0');
}

if (!$connection->tableColumnExists($tableName, 'minimum_amount_of_finance')) {
    $connection->addColumn($tableName, 'minimum_amount_of_finance', 'DECIMAL(11, 2) DEFAULT 0');
}

$tableName = $installer->getTable('rockar_financingoptions/terms');

if (!$connection->tableColumnExists($tableName, 'individual_fee_monthly')) {
    $connection->addColumn($tableName, 'individual_fee_monthly', 'DECIMAL(11, 6) DEFAULT 0');
}

if (!$connection->tableColumnExists($tableName, 'individual_fee_capitalised')) {
    $connection->addColumn($tableName, 'individual_fee_capitalised', 'DECIMAL(11, 6) DEFAULT 0');
}

if (!$connection->tableColumnExists($tableName, 'corporate_fee_monthly')) {
    $connection->addColumn($tableName, 'corporate_fee_monthly', 'DECIMAL(11, 6) DEFAULT 0');
}

if (!$connection->tableColumnExists($tableName, 'corporate_fee_capitalised')) {
    $connection->addColumn($tableName, 'corporate_fee_capitalised', 'DECIMAL(11, 6) DEFAULT 0');
}

$installer->endSetup();

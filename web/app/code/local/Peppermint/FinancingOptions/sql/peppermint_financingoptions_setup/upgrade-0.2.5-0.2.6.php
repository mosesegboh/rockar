<?php
/**
 * @category Peppermint
 * @package Peppermint_FinancingOptions
 * @author Adrian Pescar <adrian.pescar@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();
$tableName = $installer->getTable('rockar_financingoptions/data');
$columnName = 'rate_subvention_value';

if ($connection->isTableExists($tableName) && $connection->tableColumnExists($tableName, $columnName)) {
    $connection->changeColumn(
        $tableName,
        $columnName,
        $columnName,
        [
            'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'length'  => '9,3',
            'default' => '0',
            'comment' => 'Rate Subvention Value'
        ]
    );
}

$installer->endSetup();

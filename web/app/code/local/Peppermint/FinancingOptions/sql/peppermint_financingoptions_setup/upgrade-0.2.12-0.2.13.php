<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Jiraphong Witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

// @var Mage_Core_Model_Resource_Setup $installer
$installer = $this;

// @var String $tableName
$tableName = $installer->getTable('rockar_financingoptions/data');

// @var Varien_Db_Adapter_Interface $connection
$connection = $installer->getConnection();
$installer->startSetup();

if (!$connection->tableColumnExists($tableName, 'vehicle_type')) {
    $connection->addColumn($tableName, 'vehicle_type', [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 32,
        'nullable' => true,
        'default' => null,
        'comment' => 'Applicable Vehicle type'
    ]);
}

$installer->endSetup();

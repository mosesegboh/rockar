<?php

/**
 * @category  Peppermint
 * @package   Peppermint_Orderamend
 * @author    Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
/** @var Varien_Db_Adapter_Interface $connection */
$connection = $installer->getConnection();
$installer->startSetup();
$tableName = $installer->getTable('rockar_orderamend/finance_options');

if (!$connection->tableColumnExists($tableName, 'is_credit')) {
    $connection->addColumn($tableName, 'is_credit', [
        'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'nullable'  => true,
        'comment'   => 'Is Credit',
        'default'    => '0'
    ]);
}
if (!$connection->tableColumnExists($tableName, 'value_cap_limit')) {
    $connection->addColumn($tableName, 'value_cap_limit', [
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'nullable'  => true,
        'comment'   => 'Value cap limit',
        'default'    => '250000'
    ]);
}
if (!$connection->tableColumnExists($tableName, 'percentage_of_vehicle_finance')) {
    $connection->addColumn($tableName, 'percentage_of_vehicle_finance', [
        'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'nullable'  => true,
        'comment'   => 'Percentage of vehicle finance',
        'default'    => '30'
    ]);
}

$installer->endSetup();

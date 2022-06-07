<?php

/**
 * @category     Setup
 * @package      Peppermint_Setup
 * @author       Catalin Lungu <catalin.lungu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;

/** @var String $table */
$table = $installer->getTable('rockar_financingoptions/options');

/** @var Varien_Db_Adapter_Interface $connection */
$connection = $installer->getConnection();

$installer->startSetup();
$connection
    ->addColumn($table, 'value_cap_limit', [
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'nullable'  => true,
        'comment'   => 'Value cap limit',
        'default'    => '250000'
    ]);
$connection
    ->addColumn($table, 'percentage_of_vehicle_finance', [
        'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'nullable'  => true,
        'comment'   => 'Percentage of vehicle finance',
        'default'    => '30'
    ]);
$installer->endSetup();

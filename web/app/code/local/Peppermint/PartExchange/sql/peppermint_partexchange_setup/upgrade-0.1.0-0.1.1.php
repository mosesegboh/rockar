<?php

/**
 * @category     Peppermint
 * @package      Peppermint_PartExchange
 * @author       Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
/** @var $connection Varien_Db_Adapter_Interface*/
$connection = $installer->getConnection();
$installer->startSetup();
$partExchangeTables = [
    $installer->getTable('rockar_partexchange/partExchange'),
    $installer->getTable('rockar_partexchange/order'),
    $installer->getTable('rockar_partexchange/reports')
];
$column = 'car_year';

// Add column if not exists
foreach ($partExchangeTables as $table) {
    if (!$connection->tableColumnExists($table, $column)) {
        $connection->addColumn($table, $column, [
            'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'nullable' => true,
            'default'   => NULL,
            'comment'  => 'Year of first Registration',
            'after' => 'car_mileage'
        ]);
    }
}

$installer->endSetup();

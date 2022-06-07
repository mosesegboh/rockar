<?php
/**
 * @category  Peppermint
 * @package   Peppermint_PartExchange
 * @author    Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();
$partExchangeTables = [
    $installer->getTable('rockar_partexchange/partExchange'),
    $installer->getTable('rockar_partexchange/order'),
    $installer->getTable('rockar_partexchange/reports')
];
$columns = [
    'first_date_of_registration' => [
        'type' => Varien_Db_Ddl_Table::TYPE_DATE,
        'comment' => 'Vehicle date of registration'
    ],
    'make_name' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 128,
        'comment' => 'Make name'
    ],
    'make_code' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 32,
        'comment' => 'Make code'
    ],
    'original_valuation' => [
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'precision' => 15,
        'scale' => 2,
        'comment' => 'Original valuation from trade in'
    ],
    'vin' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 32,
        'comment' => 'Vehicle vin number'
    ]
];

// Add column if not exists
foreach ($partExchangeTables as $table) {
    foreach ($columns as $column => $definition) {
        if (!$connection->tableColumnExists($table, $column)) {
            $connection->addColumn($table, $column, $definition);
        }
    }
}

$installer->endSetup();

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_LeadTime
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();

$reservationTableName = $installer->getTable('peppermint_leadtime/reservation');
$columnName = 'vin';
if ($connection->isTableExists($reservationTableName)) {
    $connection->dropTable($reservationTableName);
}

$reservationTable = $connection->newTable($reservationTableName)
    ->addColumn(
        'id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        [
            'identity' => true,
            'nullable' => false,
            'unsigned' => true,
            'primary' => true
        ],
        'Primary Key Id'
    )
    ->addColumn('vin_number', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100)
    ->addColumn(
        'customer_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        [
            'nullable' => true,
            'unsigned' => true
        ]
    )
    ->addColumn(
        'created_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        ['nullable' => false]
    )
    ->addIndex(
        $installer->getIdxName($reservationTableName, ['vin_number']),
        ['vin_number'],
        ['type' => 'unique']
    );

$connection->createTable($reservationTable);

$installer->endSetup();

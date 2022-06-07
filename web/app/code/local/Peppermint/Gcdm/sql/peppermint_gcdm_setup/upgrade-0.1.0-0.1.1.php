<?php

/**
 * Peppermint Gcdm create tables
 */
/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();

$customerAccessTableName = $installer->getTable('peppermint_gcdm/customer_access');
$customerProfileTableName = $installer->getTable('peppermint_gcdm/customer_profile');

$tablesToDropIfExists = [
    'peppermint_gcdmcustomer',
    $customerAccessTableName,
    $customerProfileTableName
];

foreach ($tablesToDropIfExists as $tableToDrop) {
    if ($connection->isTableExists($tableToDrop)) {
        $connection->dropTable($tableToDrop);
    }
}

$customerAccessTable = $connection->newTable($customerAccessTableName)
    ->addColumn(
        'customer_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        11,
        [
            'unsigned' => true,
            'nullable' => false,
            'primary'   => true,
        ],
        'Customer id'
    )
    ->addColumn(
        'gcid',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        36,
        [
            'nullable'  => false
        ],
        'Customer Identification'
    )
    ->addColumn(
        'access_token',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        32,
        [
            'nullable'  => false,
        ],
        'Access Token'
    )
    ->addColumn(
        'refresh_token',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        48,
        [
            'nullable'  => false,
        ],
        'Refresh Token'
    )
    ->addForeignKey(
        $installer->getFkName('peppermint_gcdm/customer_access', 'customer_id', 'customer/entity', 'entity_id'),
        'customer_id',
        $installer->getTable('customer/entity'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    );
$connection->createTable($customerAccessTable);

$customerProfileTable = $connection->newTable($customerProfileTableName)
    ->addColumn(
        'customer_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        11,
        [
            'unsigned' => true,
            'nullable' => false,
            'primary'   => true,
        ],
        'Customer id'
    )
    ->addColumn(
        'main_address_guid',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        36,
        [
            'nullable'  => false
        ],
        'Main address GUID'
    )
    ->addColumn(
        'main_phone_guid',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        36,
        [
            'nullable'  => false
        ],
        'Main phone GUID'
    )
    ->addForeignKey(
        $installer->getFkName('peppermint_gcdm/customer_profile', 'customer_id', 'customer/entity', 'entity_id'),
        'customer_id',
        $installer->getTable('customer/entity'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    );
$connection->createTable($customerProfileTable);

$installer->endSetup();

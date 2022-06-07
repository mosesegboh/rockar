<?php

/**
 * Peppermint Gcdm create tabel
 */
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();
$table = $connection->newTable($installer->getTable('peppermint_gcdm/gcdmcustomer'))
    ->addColumn(
        'customer_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        11,
        array(
            'unsigned' => true,
            'nullable' => false,
            'primary'   => true,
        ),
        'Customer id'
    )
    ->addColumn(
        'gcid',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        255,
        array(
            'nullable'  => false
        ),
        'Customer Identification'
    )
    ->addColumn(
        'access_token',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        255,
        array(
            'nullable'  => false,
        ),
        'Access Token'
    )
    ->addColumn(
        'refresh_token',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        255,
        array(
            'nullable'  => false,
        ),
        'Refresh Token'
    );
$connection->createTable($table);
$installer->endSetup();

<?php

/**
 * @category     Peppermint
 * @package      Peppermint_ExtendedRules
 * @author       Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$connection = $installer->getConnection();
$tableName = $installer->getTable('peppermint_extendedrules/mileage');

/* Create table peppermint_extendedrules_mileage */
$connection->dropTable($tableName);
$table = $connection
    ->newTable($tableName)
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
        'ID'
    )
    ->addColumn(
        'mileage_from',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        [
            'nullable' => false,
            'default' => '0'
        ],
        'Mileage From'
    )
    ->addColumn(
        'mileage_to',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        [
            'nullable' => false,
            'default' => '0'
        ],
        'Mileage To'
    )
    ->addColumn(
        'deduction_value',
        Varien_Db_Ddl_Table::TYPE_DECIMAL,
        '12,4',
        [
            'nullable' => false,
            'default' => '0.0000'
        ],
        'Deduction value'
    )
    ->addColumn(
        'deduction_type',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        11,
        [
            'nullable' => false,
            'default' => 0
        ],
        'Deduction type'
    )
    ->setComment('Peppermint Extended Rules - Mileage');
$connection->createTable($table);

$installer->endSetup();

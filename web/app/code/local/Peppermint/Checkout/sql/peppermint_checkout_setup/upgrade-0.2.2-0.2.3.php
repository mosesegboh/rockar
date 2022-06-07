<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Checkout
 * @author    Lucian Mesaros <lucian.mesaros@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();

$tableNames = [
    $installer->getTable('rockar_checkout/quote_additional'),
    $installer->getTable('rockar_checkout/order_additional')
];

$columnsToModify = [
    'pref_method_contact_email' => [
        'type' => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'default' => '1'
    ],
    'pref_method_contact_sms' => [
        'type' => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'default' => '0'
    ],
    'pref_method_contact_normal' => [
        'type' => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'default' => '0'
    ]
];

foreach ($tableNames as $tableName) {
    foreach ($columnsToModify as $column => $dataType) {
        if ($connection->tableColumnExists($tableName, $column)) {
            $connection->modifyColumn($tableName, $column, $dataType);
        }
    }
}

$installer->endSetup();

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Localstores
 * @author    Jez Horton <jez.horton@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

// @var Mage_Core_Model_Resource_Setup $installer
$installer = $this;

// @var String $tableName
$tableName = $installer->getTable('rockar_localstores/stores');

// @var Varien_Db_Adapter_Interface $connection
$connection = $installer->getConnection();
$installer->startSetup();

if (!$connection->tableColumnExists($tableName, 'associated_compound_dealer')) {
    $connection->addColumn($tableName, 'associated_compound_dealer', [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => true,
        'default' => null,
        'length' => 255,
        'comment' => 'The compound dealer associated to a store'
    ]);
}

if (!$connection->tableColumnExists($tableName, 'is_compound_dealer')) {
    $connection->addColumn($tableName, 'is_compound_dealer', [
        'type' => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'nullable' => false,
        'default' => 0,
        'comment' => 'A bool to check if the store is compound'
    ]);
}

$installer->endSetup();
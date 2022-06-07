<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Orderamend
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

if (!$connection->tableColumnExists($tableName, 'enable_youdrive_request')) {
    $connection->addColumn($tableName, 'enable_youdrive_request', [
        'type' => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'nullable' => false,
        'default' => 0,
        'comment' => 'Enable youdrive requests'
    ]);
}

$installer->endSetup();
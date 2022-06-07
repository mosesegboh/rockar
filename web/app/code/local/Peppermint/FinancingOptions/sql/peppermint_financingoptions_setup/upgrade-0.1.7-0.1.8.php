<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

// @var Mage_Core_Model_Resource_Setup $installer
$installer = $this;

// @var String $tableName
$tableName = $installer->getTable('rockar_financingoptions/data');

// @var Varien_Db_Adapter_Interface $connection
$connection = $installer->getConnection();

$installer->startSetup();

if (!$connection->tableColumnExists($tableName, 'max_balloon_percent')) {
    $connection->addColumn($tableName, 'max_balloon_percent', [ 
            'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'length'    => '9,2',
            'nullable'  => true,
            'comment'   => 'Maximum Balloon Percentage',
            'default'   => '0'
        ]);
}

$installer->endSetup();

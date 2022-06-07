<?php 

/**
* @category  Peppermint
* @package   Peppermint\Checkout
* @author    Adrian Grigorita <adrian.grigorita@rockar.com>
* @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
*/

/** @var $this Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
$connection =  $installer->getConnection();
$tableName = $installer->getTable('rockar_checkout/quote_additional');
$columnName = 'marital_status';

if ($connection->tableColumnExists($tableName, $columnName)) {
    $connection->modifyColumn($tableName, $columnName, [
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => 5
        ]
    );  
}

$installer->endSetup();

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Orderamend
 * @author    Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

// @var $installer Mage_Core_Model_Resource_Setup
$installer = $this;
$connection = $installer->getConnection();

$installer->startSetup();

$tableName = $installer->getTable('rockar_orderamend/finance_data');

if (!$connection->tableColumnExists($tableName, 'max_balloon_percent')) {
    $connection->addColumn($tableName, 'max_balloon_percent', [
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'    => '9,2',
        'nullable'  => true,
        'comment'   => 'Max Balloon Percent',
        'default'   => '0'
    ]);
}

$installer->endSetup();

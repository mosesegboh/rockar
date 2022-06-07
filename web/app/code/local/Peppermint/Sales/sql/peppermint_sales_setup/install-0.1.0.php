<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

/** @var Mage_Core_Model_Resource_Setup $this */
$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();
$tableName = $installer->getTable('rockar_checkout/order_additional');
$column = 'expired_date';

if (!$connection->tableColumnExists($tableName, $column)) {
    $connection->addColumn(
        $tableName,
        $column,
        [
            'type' => Varien_Db_Ddl_Table::TYPE_DATE,
            'identity' => false,
            'nullable' => true,
            'comment' => 'Vehicle expired date'
        ]
    );
}

$installer->endSetup();

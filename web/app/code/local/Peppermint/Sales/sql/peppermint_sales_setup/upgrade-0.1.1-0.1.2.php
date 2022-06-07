<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Dinu Brie <dinu.brie@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();
$tableName = $installer->getTable('rockar_checkout/order_additional');

$connection->addColumn($tableName, 'designation', Varien_Db_Ddl_Table::TYPE_TEXT);
$connection->addColumn($tableName, 'contact_number', Varien_Db_Ddl_Table::TYPE_TEXT);
$connection->addColumn($tableName, 'is_company', Varien_Db_Ddl_Table::TYPE_BOOLEAN);

$installer->endSetup();

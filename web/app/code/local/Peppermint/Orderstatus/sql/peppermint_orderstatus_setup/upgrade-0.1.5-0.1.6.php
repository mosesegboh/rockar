<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Orderstatus
 * @author    Dinu Brie <dinu.brie@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();
$tableName = $installer->getTable('rockar_orderstatus/status');
$connection->addColumn($tableName, 'code', Varien_Db_Ddl_Table::TYPE_TEXT);
$installer->endSetup();

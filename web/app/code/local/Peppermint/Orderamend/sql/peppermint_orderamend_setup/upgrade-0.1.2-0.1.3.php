<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Orderamend
 * @author    Adrian Pescar <adrian.pescar@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
/** @var Varien_Db_Adapter_Interface $connection */
$connection = $installer->getConnection();
$installer->startSetup();
$tableName = $installer->getTable('rockar_orderamend/finance_options');

$connection->addColumn($tableName, 'interest_rate_calc', [
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'nullable' => true,
    'comment' => 'Calculated interest rate'
]);

$installer->endSetup();

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Adrian Pescar <adrian.pescar@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();

/* Add new columns for table rockar_financing_options: interest_rate_calc */
$tableName = $installer->getTable('rockar_financingoptions/options');
$connection->addColumn($tableName, 'interest_rate_calc', Varien_Db_Ddl_Table::TYPE_TEXT);
$installer->endSetup();

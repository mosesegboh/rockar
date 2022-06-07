<?php

/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Lucaci Stefan <lucacistefan.alexandru@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();
// get financing_options_gropu table
$tableName = $installer->getTable('rockar_financingoptions/group');
// add column balloon_slider_steps to table
if (!$connection->tableColumnExists($tableName, 'balloon_slider_steps')) {
    $connection->addColumn($tableName, 'balloon_slider_steps', 'TEXT DEFAULT NULL');
}
// add column balloon_default_step to table
if (!$connection->tableColumnExists($tableName, 'balloon_default_step')) {
    $connection->addColumn($tableName, 'balloon_default_step', 'TEXT DEFAULT NULL');
}
// close the setup
$installer->endSetup();

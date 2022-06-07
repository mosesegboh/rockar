<?php
/**
 * @category  Peppermint
 * @package   Peppermint_partExchange
 * @author    Jiraphong Witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();

$tableName = 'peppermint_partexchange_customer_session';

if ($connection->isTableExists($tableName)) {
    $connection->dropTable($tableName);
}

$installer->endSetup();

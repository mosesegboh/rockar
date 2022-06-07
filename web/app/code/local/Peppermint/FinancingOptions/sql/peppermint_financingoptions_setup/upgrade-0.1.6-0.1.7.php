<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Setup
 * @author       Mihai Chezan <mihai.chezan@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */

$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();

try {
    $tableName = $installer->getTable('rockar_financingoptions/options');

    if (!$connection->tableColumnExists($tableName, 'is_credit')) {
        $connection->addColumn($tableName, 'is_credit', 'SMALLINT(6) DEFAULT 0');
    }
} catch (Exception $e) {
    Mage::logException($e);
}

$installer->endSetup();

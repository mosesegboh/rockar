<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Admin
 * @author    Dominic Sutton <dominic.sutton@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();
$table = $installer->getTable('peppermint_admin/role');

if ($connection->isTableExists($table)) {
    $connection->addColumn(
        $table,
        'url',
        [
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => 255,
            'nullable' => false,
            'default' => Mage::getModel('peppermint_admin/config')->getBaseUrl(),
            'comment' => 'URL'
        ]
    );
}

$installer->endSetup();

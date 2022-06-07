<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Customer
 * @author    Taras Kapushchak <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

/**
 * Add new show_in_frontend  column to customer documents table
 */
$tableName = $installer->getTable('rockar_customer/documents');
$tableColumn = 'initial_filename';

if ($connection->isTableExists($tableName)) {
    if (!$connection->tableColumnExists($tableName, $tableColumn)) {
        $installer->getConnection()
            ->addColumn(
                $tableName,
                $tableColumn,
                [
                    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
                    'length' => 255,
                    'default' => null,
                    'comment' => 'Initial name of the file uploaded by customer',
                ]
            );
    }
}

$installer->endSetup();

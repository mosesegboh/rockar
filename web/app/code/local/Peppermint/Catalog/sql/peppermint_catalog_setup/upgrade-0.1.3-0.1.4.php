<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Catalog
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();
$table = $installer->getTable('peppermint_catalog/matrix_mapping');

if (!$connection->isTableExists($table)) {
    $mappingTable = $connection->newTable($table)
        ->addColumn(
            'model_matrix_carousel',
            Varien_Db_Ddl_Table::TYPE_VARCHAR,
            255,
            [
                'auto_increment' => false,
                'nullable' => false,
                'unique' => true,
                'primary' => true
            ],
            'Model Matrix description'
        )->addColumn(
            'model_carousel',
            Varien_Db_Ddl_Table::TYPE_VARCHAR,
            255,
            [
                'nullable' => false
            ],
            'Model description'
        );
    $connection->createTable($mappingTable);
}

$installer->endSetup();

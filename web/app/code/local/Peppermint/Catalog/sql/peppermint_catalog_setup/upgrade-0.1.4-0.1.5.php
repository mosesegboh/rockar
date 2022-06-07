<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Catalog
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();
$table = $installer->getTable('peppermint_catalog/matrix_mapping');

$columnsToAdd = [
    'brand' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 32,
        'comment' => 'Brand'
    ],
    'position' => [
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'default'   => 0,
        'comment' => 'Position'
    ]
];

foreach ($columnsToAdd as $column => $dataType) {
    if (!$connection->tableColumnExists($table, $column)) {
        $connection->addColumn($table, $column, $dataType);
    }
}

$installer->endSetup();

<?php
/**
 * @category  Peppermint
 * @package   Peppermint\Catalog
 * @author    Andrian Kogoshvili <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();
$table = $installer->getTable('catalog/product_option_type_value');

$columnsToAdd = [
    'highligted' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 1,
        'comment' => 'Should Image be Displayed'
    ],
    'cosy_url' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'comment' => 'Image Url'
    ]
];

foreach ($columnsToAdd as $column => $dataType) {
    if (!$connection->tableColumnExists($table, $column)) {
        $connection->addColumn($table, $column, $dataType);
    }
}

$installer->endSetup();

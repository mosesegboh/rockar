<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Accessories
 * @author    Taras Kapushchak <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

$this->startSetup();
$connection = $this->getConnection();

$clearTables = [
    $this->getTable('rockar_accessories/accessories'),
    $this->getTable('rockar_accessories/accessories_group'),
    $this->getTable('rockar_accessories/accessories_relations'),
];

// We have to clear tables before adding new field to avoid conflicts
foreach ($clearTables as $clearTable) {
    $connection->truncateTable($clearTable);
}

$table = $this->getTable('rockar_accessories/accessories');

// Adding material_number column to rockar_accessories table
if (!$connection->tableColumnExists($table, 'material_number')) {
    $connection->addColumn($table, 'material_number', [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 255,
        'comment' => 'Material Number',
        'after' => 'identifier',
    ]);
}

$this->endSetup();

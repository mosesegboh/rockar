<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Catalog
 * @author    Taras Kapushchak <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();
$table = $installer->getTable('peppermint_catalog/matrix_mapping');

$columnsToAdd = [
    'run_out_date' => [
        'type' => Varien_Db_Ddl_Table::TYPE_DATE,
        'comment' => 'Run Out Date'
    ]
];

foreach ($columnsToAdd as $column => $dataType) {
    if (!$connection->tableColumnExists($table, $column)) {
        $connection->addColumn($table, $column, $dataType);
    }
}

$installer->endSetup();

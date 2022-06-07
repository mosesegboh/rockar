<?php
/**
 * @category  Peppermint
 * @package   Peppermint_YouDrive
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();

$table = $installer->getTable('rockar_youdrive/booking_item');

if (!$connection->tableColumnExists($table, 'vehicle_id')) {
    $connection->addColumn(
        $table,
        'vehicle_id',
        [
            'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'comment' => 'YouDrive vehicle id',
            'nullable' => false
        ]
    );
}

$connection->changeColumn(
    $installer->getTable('rockar_youdrive/booking'),
    'assigned_to',
    'assigned_to',
    [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 255
    ]
);

$installer->endSetup();

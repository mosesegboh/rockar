<?php
/**
 * @category  Peppermint
 * @package   Peppermint_YouDrive
 * @author    Alexander Metzgen <alexander.metzgen@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();

$table = $installer->getTable('rockar_youdrive/booking');

if ($connection->isTableExists($table) && !$connection->tableColumnExists($table, 'booking_placed')) {
    $connection->addColumn(
        $table,
        'booking_placed',
        [
            'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'comment' => 'Test drive booking placed in-store or online',
            'nullable' => true
        ]
    );
}

$installer->endSetup();

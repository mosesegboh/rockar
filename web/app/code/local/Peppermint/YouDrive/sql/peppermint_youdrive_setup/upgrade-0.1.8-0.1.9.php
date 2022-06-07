<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Youdrive
 * @author    Ausma Smite <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();

$table = $installer->getTable('rockar_youdrive/booking');

if ($connection->isTableExists($table) && !$connection->tableColumnExists($table, 'dealer_id')) {
    $connection->addColumn(
        $table,
        'dealer_id',
        [
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'comment' => 'Dealer ID',
            'length' => 255,
            'nullable' => true
        ]
    );
}

$installer->endSetup();

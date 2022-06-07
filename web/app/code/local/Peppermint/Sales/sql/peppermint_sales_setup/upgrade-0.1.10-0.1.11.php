<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Osama Ahmed <osama.ahmed@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (https://rockar.com)
 */

$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();
$additionalData = $installer->getTable('peppermint_sales/additional_data');
$column = 'can_amend';

// Add column if not exists
if (!$connection->tableColumnExists($additionalData, $column)) {
    $connection->addColumn(
        $additionalData,
        $column,
        [
            'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'nullable' => true,
            'default'   => NULL,
            'comment'  => 'Determines whether an order amendment is locked or unlocked'
        ]
    );
}

$installer->endSetup();

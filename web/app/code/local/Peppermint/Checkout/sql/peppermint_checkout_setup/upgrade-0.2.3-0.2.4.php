<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Checkout
 * @author    Robert Onac <robert.onac@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

/** @var Mage_Core_Model_Resource_Setup $this */
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();

$tableQuoteAdditional = $installer->getTable('rockar_checkout/quote_additional');
$columnsToAdd = [
    'designation' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 5,
        'comment' => 'Designation'
    ],
    'contact_number' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 20,
        'comment' => 'Contact Number'
    ],
    'is_company' => [
        'type' => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'comment' => 'Is company'
    ]
];

foreach ($columnsToAdd as $column => $dataType) {
    if (!$connection->tableColumnExists($tableQuoteAdditional, $column)) {
        $connection->addColumn($tableQuoteAdditional, $column, $dataType);
    }
}

$installer->endSetup();

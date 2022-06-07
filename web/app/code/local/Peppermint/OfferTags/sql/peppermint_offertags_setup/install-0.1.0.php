<?php
/**
 * @category  Peppermint
 * @package   Peppermint_OfferTags
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$offerTagsTable = $installer->getTable('peppermint_offertags/offertags');

$connection = $installer->getConnection();
$installer->startSetup();

/**
 * Create table 'peppermint_offertags'
 */
$table = $connection->newTable($offerTagsTable)
    ->addColumn('offertag_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true
    ], 'Offer Tag ID')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255)
    ->addColumn('action_type', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255)
    ->addColumn('label', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255)
    ->addColumn('icon',
        Varien_Db_Ddl_Table::TYPE_TEXT,
        null,
        ['nullable' => true],
        'Icon Image'
    )
    ->addColumn('brand_bg_color',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        null,
        [
            'unsigned' => true,
            'nullable' => false,
            'default' => '0'
        ],
        'Fill Background with Brand Color')
    ->addColumn('sort_order', Varien_Db_Ddl_Table::TYPE_INTEGER, 11)
    ->setComment('Offer Tags Table');

$connection->createTable($table);

$installer->endSetup();

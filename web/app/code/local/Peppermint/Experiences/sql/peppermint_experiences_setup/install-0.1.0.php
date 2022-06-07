<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Experiences
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$experiencesTable = $installer->getTable('peppermint_experiences/experiences');
$connection = $installer->getConnection();
$installer->startSetup();

/**
 * Create table 'peppermint_experiences'
 */
$table = $connection->newTable($experiencesTable)
    ->addColumn('experience_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true
    ], 'Experience ID')
    ->addColumn('name',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        255,
        ['unique' => true],
        'Experience Name'
    )
    ->addColumn('label', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255)
    ->addColumn('textblock', Varien_Db_Ddl_Table::TYPE_TEXT, '64k')
    ->addColumn('image',
        Varien_Db_Ddl_Table::TYPE_TEXT,
        null,
        ['nullable' => true],
        'Item Image'
    )
    ->setComment('Experiences Table');

$connection->createTable($table);

$installer->endSetup();

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

if (!$connection->tableColumnExists($experiencesTable, 'link_label')) {
    $connection->addColumn($experiencesTable, 'link_label', [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 255,
        'nullable' => false,
        'default' => 'Find out more',
        'comment' => '`Find out more` link label'
    ]);
}

if (!$connection->tableColumnExists($experiencesTable, 'link_url')) {
    $connection->addColumn($experiencesTable, 'link_url', [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 2000,
        'nullable' => true,
        'default' => null,
        'comment' => '`Find out more` link URL'
    ]);
}
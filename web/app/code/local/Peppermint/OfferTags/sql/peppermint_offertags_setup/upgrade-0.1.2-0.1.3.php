<?php
/**
 * @category  Peppermint
 * @package   Peppermint_OfferTags
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$table = $installer->getTable('peppermint_offertags/offertags');
$connection = $installer->getConnection();

$connection->addIndex(
    $table,
    $installer->getIdxName(
        'peppermint_offertags/offertags',
        ['name'],
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    ['name'],
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$installer->endSetup();

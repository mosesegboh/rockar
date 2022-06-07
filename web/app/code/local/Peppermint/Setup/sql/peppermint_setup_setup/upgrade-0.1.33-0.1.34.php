<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Setup
 * @author    Taras Kapushchak <techteam@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();

// Remove Rockar_RoadFundLicense tables
$tables = [
    'rockar_roadfundlicense_taxrate',
    'rockar_roadfundlicense_taxperiod',
    'rockar_roadfundlicense_approved_used_luxury_tax',
    'rockar_roadfundlicense_index',
    'rockar_roadfundlicense_approved_used_taxperiod',
    'rockar_roadfundlicense_approved_used_taxrate',
];

foreach ($tables as $table) {
    $tableName = $installer->getTable($table);

    if ($installer->tableExists($tableName)) {
        $connection->dropTable($tableName);
    }
}

// Remove Rockar_RoadFundLicense CMS blocks
$cmsBlocks = [
    'new_ved_info_block',
    'au_ved_info_block',
];

foreach ($cmsBlocks as $cmsBlock) {
    Mage::getModel('cms/block')->load($cmsBlock, 'identifier')->delete();
}

// Remove Rockar_RoadFundLicense record
$installer->run("DELETE FROM {$this->getTable('core_resource')}  WHERE `code` = 'rockar_roadfundlicense_setup'");


$installer->endSetup();

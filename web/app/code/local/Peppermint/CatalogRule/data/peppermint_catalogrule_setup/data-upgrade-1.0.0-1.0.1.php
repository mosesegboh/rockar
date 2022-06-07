<?php
/**
 * @category  Peppermint
 * @package   Peppermint_CatalogRule
 * @author    Jiraphong Witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

/** @var $this Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
$connection =  $installer->getConnection();
$demoStoreCode = Mage::helper('peppermint_all/store')->getDemoStoreCode();

foreach (Mage::app()->getWebsites() as $websiteId => $website) {
    if ($website->getCode() === $demoStoreCode) {
        $connection->delete($installer->getTable('catalogrule/website'), ['website_id = ?' => $websiteId]);
        break;
    }
}

$installer->endSetup();

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Setup
 * @author    Jiraphong Witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();

$websiteIds = Mage::getModel('core/website')->getCollection()
    ->addFieldToSelect('website_id')
    ->addFieldToFilter('code', ['neq' => 'demo'])
    ->getColumnValues('website_id');

// Need to add admin store for order amend
$websiteIds[] =  Mage_Core_Model_App::ADMIN_STORE_ID;

$pxPromoRule = Mage::getModel('rockar_partexchange/promotions_rule')->getCollection()
    ->addFieldToFilter('scope', 'shortfall');

foreach ($pxPromoRule as $key => $value) {
    $value->setWebsiteIds($websiteIds)
        ->save();
}

$installer->endSetup();

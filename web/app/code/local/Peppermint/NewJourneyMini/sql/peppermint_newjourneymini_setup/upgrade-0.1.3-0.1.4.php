<?php
/**
 * @category  Peppermint
 * @package   Peppermint_NewJourney
 * @author    Andrian Kogoshvili <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();
$miniStoreId = Mage::getModel('core/store')->load('mini_store_view', 'code')->getId();

Mage::getModel('core/config_data')
    ->setValue('stores/' . $miniStoreId . '/mini-logo-ondark.png')
    ->setPath(Peppermint_All_Helper_NewJourney::XML_PATH_DESIGN_WEBSITE_LOGO_LANDING_PAGE)
    ->setScope('stores')
    ->setScopeId($miniStoreId)
    ->save();

Mage::getModel('core/config_data')
    ->setValue('stores/' . $miniStoreId . '/mini-logo-onlight.png')
    ->setPath(Peppermint_All_Helper_NewJourney::XML_PATH_DESIGN_WEBSITE_LOGO_OTHER_PAGES)
    ->setScope('stores')
    ->setScopeId($miniStoreId)
    ->save();

Mage::helper('peppermint_all/migrateImages')->copyMediaFiles(['mini-logo-ondark.png'], 'rockar', 'mini2', 'website/navigation/logo/stores/' . $miniStoreId, '');
Mage::helper('peppermint_all/migrateImages')->copyMediaFiles(['mini-logo-onlight.png'], 'rockar', 'mini2', 'website/navigation/logo_other/stores/' . $miniStoreId, '');

$installer->endSetup();

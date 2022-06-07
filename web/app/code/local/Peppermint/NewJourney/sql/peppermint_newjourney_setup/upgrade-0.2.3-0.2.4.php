<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Setup
 * @author    Nikoloz Gabunia <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();
$bmwStoreId = Mage::getModel('core/store')->load('bmw_store_view', 'code')->getId();

if (!Mage::getModel('cms/block')->load('est_value_disclaimer')->getId()) {
    $block = Mage::getModel('cms/block');
    $block->setTitle('Estimated Value Disclaimer');
    $block->setIdentifier('est_value_disclaimer');
    $block->setStores([$bmwStoreId]);
    $block->setIsActive(1);
    $block->setContent(
        '<p>*The "Trade-In Value" of your vehicle is an estimate provided and many factors that cannot be assessed without a physical inspection of the vehicle may affect actual value. The final or actual "Trade-In Value" for your vehicle will be confirmed upon physical inspection of your vehicle having been undertaken by an authorised and approved BMW retailer.</p>'
    );
    $block->save();
}

$installer->endSetup();

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_NewJourney
 * @author    Andrian Kogoshvili <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();
$bmwStoreId = Mage::getModel('core/store')->load('bmw_store_view', 'code')->getId();
$miniStoreId = Mage::getModel('core/store')->load('mini_store_view', 'code')->getId();

// fix: make block store specific
if (Mage::getModel('cms/block')->setStoreId($bmwStoreId)->load('hero_image_block')->getId()) {
    $block = Mage::getModel('cms/block')->load('hero_image_block');
    $block->setTitle('BMW Hero Image Block');
    $block->setStores([$bmwStoreId]);
    $block->save();
}

if (!Mage::getModel('cms/block')->setStoreId($miniStoreId)->load('hero_image_block')->getId()) {
    $block = Mage::getModel('cms/block');
    $block->setTitle('MINI Hero Image Block');
    $block->setIdentifier('hero_image_block');
    $block->setStores([$miniStoreId]);
    $block->setIsActive(1);
    $block->setContent('');
    $block->save();
}

$installer->endSetup();

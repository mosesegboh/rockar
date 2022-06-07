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
$block = Mage::getModel('cms/block')->load('navigation_heading');
$block->setTitle('BMW Navigation Heading');
$block->setStores([$bmwStoreId]);
$block->save();

if (!Mage::getModel('cms/block')->setStoreId($miniStoreId)->load('navigation_heading')->getId()) {
    $block = Mage::getModel('cms/block');
    $block->setTitle('MINI Navigation Heading');
    $block->setIdentifier('navigation_heading');
    $block->setStores([$miniStoreId]);
    $block->setIsActive(1);
    $block->setContent(
        '<div class="nav-heading">' .
            '<span class="nav-heading-title white">The Journey to Fun</span>' .
            '<span class="nav-heading-title white">starts here.</span>' .
            '<span class="nav-heading-subtitle desktop-only">Browse and buy your mini online.</span>' .
        '</div>'
    );
    $block->save();
}

$installer->endSetup();

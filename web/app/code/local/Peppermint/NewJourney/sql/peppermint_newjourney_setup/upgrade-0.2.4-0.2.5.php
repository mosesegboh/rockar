<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Setup
 * @author    Mariam Khelashvili <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();
$bmwStoreId = Mage::getModel('core/store')->load('bmw_store_view', 'code')->getId();

$block = [
    'identifier' => 'navigation_heading_test_drives',
    'title' => 'Navigation Heading Test Drives',
    'content' =>
        '<div class="nav-heading">' .
        '<h2 class="h1 white">TEST DRIVE</h2>' .
        '<h2 class="h1 white">YOUR DREAM BMW.</h2>' .
        '<h4 class="desktop-only">Book your test drive online today.</h4>' .
        '</div>'
];

if (!Mage::getModel('cms/block')->load('navigation_heading_test_drives')->getId()) {
    $block = Mage::getModel('cms/block');
    $block->setTitle('Navigation Heading Test Drives');
    $block->setIdentifier('navigation_heading_test_drives');
    $block->setStores([$bmwStoreId]);
    $block->setIsActive(1);
    $block->setContent(
        '<div class="nav-heading">' .
        '<h2 class="h1 white">TEST DRIVE</h2>' .
        '<h2 class="h1 white">YOUR DREAM BMW.</h2>' .
        '<h4 class="desktop-only">Book your test drive online today.</h4>' .
        '</div>'
    );
    $block->save();
}

if (!Mage::getModel('cms/block')->load('hero_image_block_test_drives')->getId()) {
    $block = Mage::getModel('cms/block');
    $block->setTitle('Hero Image Block Test Drives');
    $block->setIdentifier('hero_image_block_test_drives');
    $block->setStores([$bmwStoreId]);
    $block->setIsActive(1);
    $block->setContent(
        <<<EOF
            <img src="{{media url="wysiwyg/Hero-Image_1_test_drive.jpg"}}" alt="" />
EOF
    );
    $block->save();
}

Mage::helper('peppermint_all/migrateImages')->copyMediaFiles(['Hero-Image_1_test_drive.jpg'], 'rockar', 'bmw2', 'wysiwyg');

$installer->endSetup();

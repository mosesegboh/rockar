<?php
/**
 * @category  Peppermint
 * @package   Peppermint_NewJourneyMini
 * @author    Mariam Khelashvili <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();
$miniStoreId = Mage::getModel('core/store')->load('mini_store_view', 'code')->getId();
$headerBlock = Mage::getModel('cms/block')->setStoreId($miniStoreId)->load('navigation_heading_test_drives');
$heroImageBlockDesktop = Mage::getModel('cms/block')->setStoreId($miniStoreId)->load('hero_image_block_test_drives');
$heroImageBlockMobile = Mage::getModel('cms/block')->setStoreId($miniStoreId)->load('hero_image_block_test_drives-mobile');

if (!$headerBlock->getId()) {
    $block = Mage::getModel('cms/block');
    $block->setTitle('Navigation Heading Test Drives');
    $block->setIdentifier('navigation_heading_test_drives');
    $block->setStores([$miniStoreId]);
    $block->setIsActive(1);
    $block->setContent(<<<EOF
<div class="nav-heading">
<h2 class="h1 white">TEST DRIVE</h2>
<h2 class="h1 white">YOUR DREAM MINI.</h2>
<h4 class="content">BOOK YOUR TEST DRIVE ONLINE TODAY.</h4>
</div>
EOF
    );
    $block->save();
} else {
    $headerBlock->setStores([$miniStoreId])
        ->setIsActive(1)
        ->setContent(<<<EOF
<div class="nav-heading">
<h2 class="h1 white">TEST DRIVE</h2>
<h2 class="h1 white">YOUR DREAM MINI.</h2>
<h4 class="content">BOOK YOUR TEST DRIVE ONLINE TODAY.</h4>
</div>
EOF
        )->save();
}

if (!$heroImageBlockDesktop->getId()) {
    $block = Mage::getModel('cms/block');
    $block->setTitle('Hero Image Block Test Drives');
    $block->setIdentifier('hero_image_block_test_drives');
    $block->setStores([$miniStoreId]);
    $block->setIsActive(1);
    $block->setContent(<<<EOF
            <img src="{{media url="wysiwyg/Hero-Image-Mini_test_drive.png"}}" alt="" />
EOF
    );
    $block->save();
} else {
    $heroImageBlockDesktop->setStores([$miniStoreId])
        ->setIsActive(1)
        ->setContent(<<<EOF
            <img src="{{media url="wysiwyg/Hero-Image-Mini_test_drive.png"}}" alt="" />
EOF
        )->save();
}

if (!$heroImageBlockMobile->getId()) {
    $block = Mage::getModel('cms/block');
    $block->setTitle('Hero Image Block Test Drives - MOBILE');
    $block->setIdentifier('hero_image_block_test_drives-mobile');
    $block->setStores([$miniStoreId]);
    $block->setIsActive(1);
    $block->setContent(<<<EOF
            <img src="{{media url="wysiwyg/Hero-Image-Mini_test_drive-mobile.png"}}" alt="" />
EOF
    );
    $block->save();
} else {
    $heroImageBlockMobile->setStores([$miniStoreId])
        ->setIsActive(1)
        ->setContent(<<<EOF
            <img src="{{media url="wysiwyg/Hero-Image-Mini_test_drive-mobile.png"}}" alt="" />
EOF
        )->save();
}

Mage::helper('peppermint_all/migrateImages')->copyMediaFiles(['Hero-Image-Mini_test_drive.png'], 'rockar', 'mini2', 'wysiwyg');
Mage::helper('peppermint_all/migrateImages')->copyMediaFiles(['Hero-Image-Mini_test_drive-mobile.png'], 'rockar', 'mini2', 'wysiwyg');

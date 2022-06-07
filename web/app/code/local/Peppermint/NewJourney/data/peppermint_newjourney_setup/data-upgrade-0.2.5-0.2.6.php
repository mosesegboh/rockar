<?php
/**
 * @category  Peppermint
 * @package   Peppermint_NewJourney
 * @author    Mariam Khelashvili <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();
$bmwStoreId = Mage::getModel('core/store')->load('bmw_store_view', 'code')->getId();

$cmsBlock = Mage::getModel('cms/block')->getCollection()
    ->addFieldToFilter('identifier', 'hero_image_block_test_drives')
    ->addStoreFilter($bmwStoreId, false)
    ->getFirstItem();

if ($cmsBlock->getId()) {
    $cmsBlock->setContent(
        <<<EOF
            <img src="{{media url="wysiwyg/Hero-Image_test_drive.jpg"}}" alt="" />
EOF
    )
        ->setStores([$bmwStoreId])
        ->save();
} else {
    Mage::getModel('cms/block')
        ->setIdentifier('hero_image_block_test_drives')
        ->setTitle('Hero Image Block Test Drives')
        ->setIsActive(1)
        ->setStores([$bmwStoreId])
        ->setContent(<<<EOF
            <img src="{{media url="wysiwyg/Hero-Image_test_drive.jpg"}}" alt="" />
EOF
        )
        ->save();
}

Mage::helper('peppermint_all/migrateImages')->copyMediaFiles(['Hero-Image_test_drive.jpg'], 'rockar', 'dsp2', 'wysiwyg');

$installer->endSetup();

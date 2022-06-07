<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Setup
 * @author    Nikoloz Gabunia <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();

if (!Mage::getModel('cms/block')->load('hero_image_block')->getId()) {
    $block = Mage::getModel('cms/block');
    $block->setTitle('Hero Image Block');
    $block->setIdentifier('hero_image_block');
    $block->setStores([0]);
    $block->setIsActive(1);
    $block->setContent('');
    $block->save();
}

$installer->endSetup();
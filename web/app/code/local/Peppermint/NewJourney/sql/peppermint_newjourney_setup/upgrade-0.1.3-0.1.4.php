<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Setup
 * @author    Nikoloz Gabunia <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();

if (!Mage::getModel('cms/block')->load('navigation_heading')->getId()) {
    $block = Mage::getModel('cms/block');
    $block->setTitle('Navigation Heading');
    $block->setIdentifier('navigation_heading');
    $block->setStores([0]);
    $block->setIsActive(1);
    $block->setContent(
        '<div class="nav-heading">' .
        '<h2 class="h1 white">DREAM BIG.</h2>' .
        '<h2 class="h1 white">BUY ONLINE.</h2>' .
        '<h4 class="desktop-only">BROWSE STOCK AND PURCHASE YOUR PERFECT BMW.</h4>' .
        '</div>'
    );
    $block->save();
}

$installer->endSetup();

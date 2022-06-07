<?php
/**
 * @category Peppermint
 * @package Peppermint_Setup
 * @author Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (https://rockar.com)
 */

$installer = $this;
$installer->startSetup();

$collection = Mage::getModel('scandi_menumanager/item')->getCollection()
    ->addFieldToFilter('title', ['in' => ['Test Drive', 'Test Ride']]);

foreach ($collection as $item) {
    $item->setUrl('test-drives')
        ->setType(Scandi_MenuManager_Model_Item::TYPE_SAME_WINDOW)
        ->save();
}

$installer->endSetup();

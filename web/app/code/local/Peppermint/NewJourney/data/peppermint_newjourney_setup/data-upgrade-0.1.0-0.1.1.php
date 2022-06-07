<?php
/**
 * @category Peppermint
 * @package Peppermint_Setup
 * @author Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (https://rockar.com)
 */

$installer = $this;
$installer->startSetup();

$bmwStoreId = Mage::getModel('core/store')->load('bmw_store_view', 'code')->getId();
$menuId = Mage::getModel('scandi_menumanager/menu')->getCollection()
    ->addStoreFilter($bmwStoreId)->getFirstItem()->getId();

$menuItems = Mage::getModel('scandi_menumanager/item')->getCollection()
    ->addMenuFilter($menuId)
    ->addFieldToFilter('title', 'Buy Now');

foreach ($menuItems as $item) {
    $item->setTitle('Buy Online')->save();
}

$menuItems->clear()->getSelect()->reset(Zend_Db_Select::WHERE);
$menuItems->addMenuFilter($menuId)->addFieldToFilter('title', 'Test Drive');

foreach ($menuItems as $item) {
    $item->setPosition(20)->save();
}

$menuItems->clear()->getSelect()->reset(Zend_Db_Select::WHERE);
$menuItems->addMenuFilter($menuId)->addFieldToFilter('title', 'Models');

foreach ($menuItems as $item) {
    $item->setTitle('Learn More')->setUrl('https://www.bmw.co.za/en/index.html')->save();
}

$installer->endSetup();

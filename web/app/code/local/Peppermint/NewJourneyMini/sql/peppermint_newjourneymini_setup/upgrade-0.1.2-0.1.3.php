<?php
/**
 * @category  Peppermint
 * @package   Peppermint_NewJourney
 * @author    Andrian Kogoshvili <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();
$miniStoreId = Mage::getModel('core/store')->load('mini_store_view', 'code')->getId();

$adapter = $this->getConnection();
$select = $adapter->select()
    ->from('scandi_menumanager_menu_store')
    ->where('store_id = ' . $miniStoreId)
    ->limit(1);
$data = $adapter->fetchRow($select);

$miniStoreMenuId = $data['menu_id'];

$dataToSave = [
    [
        'title' => 'Home',
        'url' => '/',
        'type' => 'same_window',
        'position' => '10'
    ],
    [
        'title' => 'Finance Offers',
        'url' => 'https://www.mini.co.za/en_ZA/home/offer-section/New_Offers.html',
        'type' => 'new_window',
        'position' => '30'
    ],
    [
        'title' => 'Find a Dealer',
        'url' => 'https://www.mini.co.za/en_ZA/home/mini-centres/mini-dealer-locator.html',
        'type' => 'new_window',
        'position' => '40'
    ]
];

foreach ($dataToSave as $value) {
    Mage::getModel('scandi_menumanager/item')
        ->setMenuId($miniStoreMenuId)
        ->setParentId('0')
        ->setTitle($value['title'])
        ->setUrl($value['url'])
        ->setType($value['type'])
        ->setCssClass('nav-link')
        ->setPosition($value['position'])
        ->setIsActive(1)
        ->save();
}

$installer->getConnection()
    ->update(
        'scandi_menumanager_menu_item',
        ['is_active' => 0],
        [
            'menu_id = ?' => $miniStoreMenuId,
            'title = ?' => 'Models'
        ]
    );

$installer->getConnection()
    ->update(
        'scandi_menumanager_menu_item',
        ['is_active' => 0],
        [
            'menu_id = ?' => $miniStoreMenuId,
            'title = ?' => 'Buy Your MINI'
        ]
    );

$installer->getConnection()
    ->update(
        'scandi_menumanager_menu_item',
        ['position' => 20],
        [
            'menu_id = ?' => $miniStoreMenuId,
            'title = ?' => 'Test Drive'
        ]
    );

$installer->endSetup();

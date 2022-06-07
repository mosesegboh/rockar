<?php
/**
 * @category  Peppermint
 * @package   Peppermint_YouDrive
 * @author    Taras Kapushchak <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

/** @var Mage_Sales_Model_Entity_Setup $installer */
$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();


$onlineBookingDisclaimerBlock = [
    'title' => 'Test Drive Online Booking Disclaimer',
    'identifier' => 'youdrive_online_booking_disclaimer',
    'content' => '<strong>Please note:</strong> The vehicle image shown ' .
        'reflects the base specification of the vehicle you have requested ' .
        'to test drive. The vehicle you test drive may be a different model ' .
        'year, different colour and have optional features that differ from ' .
        'the specification displayed. A valid Driver\'s License or Learner\'s ' .
        'license is required. You must be accompanied by an employee of the ' .
        'retailer during your test drive. The test drive experience may not ' .
        'exceed 1 hour.',
    'stores' => 0,
    'is_active' => 1
];

Mage::getModel('cms/block')->load($onlineBookingDisclaimerBlock['identifier'])
    ->addData($onlineBookingDisclaimerBlock)
    ->save();

$personalDetailsBlock = [
    'bmw_store_view' => [
        'title' => 'Test Drive Next Step Statement',
        'identifier' => 'youdrive_next_step_statement',
        'content' => 'Having trouble deciding which BMW to choose? You can book ' .
            'another test drive and compare the Sheer Driving Pleasure.',
        'stores' => 0,
        'is_active' => 1
    ],
    'mini_store_view' => [
        'title' => 'Test Drive Next Step Statement',
        'identifier' => 'youdrive_next_step_statement',
        'content' => 'Having trouble deciding which MINI to choose? You can book ' .
            'another test drive and compare the Sheer Driving Pleasure.',
        'stores' => 0,
        'is_active' => 1
    ],
    'motorrad_store_view' => [
        'title' => 'Test Drive Next Step Statement',
        'identifier' => 'youdrive_next_step_statement',
        'content' => 'Having trouble deciding which Motorrad to choose? You can book ' .
            'another test drive and compare the Sheer Driving Pleasure.',
        'stores' => 0,
        'is_active' => 1
    ]
];

foreach ($personalDetailsBlock as $storeCode => $blockData) {
    $storeId = Mage::getModel('core/store')->load($storeCode, 'code')->getId();
    $blockData['stores'] = $storeId;
    Mage::getModel('cms/block')->setStoreId($storeId)
        ->load($blockData['identifier'])
        ->addData($blockData)
        ->save();
}

$installer->endSetup();

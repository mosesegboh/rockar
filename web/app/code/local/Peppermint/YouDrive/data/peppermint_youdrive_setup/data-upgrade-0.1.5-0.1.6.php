<?php
/**
 * @category  Peppermint
 * @package   Peppermint_YouDrive
 * @author    Krists Dadzitis <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (https://rockar.com)
 */

$this->startSetup();

try {
    $storeIdentifiers = [
        'bmw_store_view',
        'mini_store_view',
        'motorrad_store_view'
    ];

    //get id for each store code
    $storeCodes = [];

    foreach ($storeIdentifiers as $id) {
        $storeCodes[$id] = Mage::getSingleton('core/store')->load($id, 'code')->getId();
    }

    //remove old block
    $block = Mage::getModel('cms/block')
        ->setStoreId(0)
        ->load('youdrive_online_booking_disclaimer', 'identifier');

    if ($block->getId()) {
        $block->delete();
    }

    $cmsBlocks = [
        [
            'identifier' => 'youdrive_howitworks',
            'title' => 'YouDrive How It Works BMW',
            'content' => '<strong>How It Works</strong>
                <p>Book your BMW test drive online in 4 simple steps.</p>
                <ul>
                    <li>Choose your vehicle</li>
                    <li>Select a location</li>
                    <li>Pick a convenient date and time</li>
                    <li>Confirm your booking</li>
                </ul>
                <p>If your model of interest isn\'t available online, you can still send an enquiry to your BMW Retailer to arrange the experience.</p>',
            'stores' => $storeCodes['bmw_store_view'],
            'is_active' => 1
        ],
        [
            'identifier' => 'youdrive_tncs',
            'title' => 'YouDrive Ts&Cs BMW',
            'content' => '<strong>Terms and Conditions</strong>
                <p>Before you make your booking, please take note of the following</p>
                <ul>
                    <li>Valid Driver\'s License or Learner\'s license required</li>
                    <li>You must be accompanied by an employee of the retailer</li>
                    <li>Test Drive experience may not exceed 1 hour</li>
                </ul>',
            'stores' => $storeCodes['bmw_store_view'],
            'is_active' => 1
        ],
        [
            'identifier' => 'youdrive_howitworks',
            'title' => 'YouDrive How It Works MINI',
            'content' => '<strong>How It Works</strong>
                <p>Book your MINI test drive online in 4 simple steps.</p>
                <ul>
                    <li>Choose your MINI</li>
                    <li>Select a location</li>
                    <li>Pick a convenient date and time</li>
                    <li>Confirm your booking</li>
                </ul>
                <p>If your model of interest isn\'t available online, you can still send an enquiry to your MINI Retailer to arrange the experience.</p>',
            'stores' => $storeCodes['mini_store_view'],
            'is_active' => 1
        ],
        [
            'identifier' => 'youdrive_tncs',
            'title' => 'YouDrive Ts&Cs MINI',
            'content' => '<strong>Ts & Cs</strong>
                <p>Before you make your booking, please take note of the following</p>
                <ul>
                    <li>Valid Driver\'s License or Learner\'s license required</li>
                    <li>You must be accompanied by an employee of the retailer</li>
                    <li>Test Drive experience may not exceed 1 hour</li>
                </ul>',
            'stores' => $storeCodes['mini_store_view'],
            'is_active' => 1
        ],
        [
            'identifier' => 'youdrive_howitworks',
            'title' => 'YouDrive How It Works MOTORRAD',
            'content' => '<strong>How It Works</strong>
                <p>Book your BMW motorcycle test ride in 4 simple steps.</p>
                <ul>
                    <li>Choose your motorcycle</li>
                    <li>Select a location</li>
                    <li>Pick a convenient date and time</li>
                    <li>Confirm your booking</li>
                </ul>
                <p>If your model of interest isn\'t available online, you can still send an enquiry to your BMW Motorrad Retailer to arrange the experience</p>',
            'stores' => $storeCodes['motorrad_store_view'],
            'is_active' => 1
        ],
        [
            'identifier' => 'youdrive_tncs',
            'title' => 'YouDrive Ts&Cs MOTORRAD',
            'content' => '<strong>Ts & Cs</strong>
                <p>Before you make your booking, please take note of the following</p>
                <ul>
                    <li>Valid Motorcycle License or Learner\'s license required</li>
                    <li>You must be accompanied by an employee of the retailer</li>
                    <li>Test Ride experience may not exceed 1 hour</li>
                </ul>',
            'stores' => $storeCodes['motorrad_store_view'],
            'is_active' => 1
        ],
        [
            'identifier' => 'youdrive_get_in_touch',
            'title' => 'YouDrive Get In Touch BMW',
            'content' => '<p>Your BMW Retailer doesn\'t currently have your model of interest available for online 
                bookings but will be in touch to arrange your test drive once you\'ve submitted your enquiry.</p>',
            'stores' => $storeCodes['bmw_store_view'],
            'is_active' => 1
        ],
        [
            'identifier' => 'youdrive_get_in_touch',
            'title' => 'YouDrive Get In Touch MINI',
            'content' => '<p>Your MINI Retailer might not currently have your model of interest available for online 
                bookings but they will contact you to arrange your experience once you\'ve sent an enquiry.</p>',
            'stores' => $storeCodes['mini_store_view'],
            'is_active' => 1
        ],
        [
            'identifier' => 'youdrive_get_in_touch',
            'title' => 'YouDrive Get In Touch MOTORRAD',
            'content' => '<p>Your BMW Motorrad Retailer might not currently have your model of interest available 
                for online bookings but they will contact you to arrange your experience once you\'ve sent an enquiry</p>',
            'stores' => $storeCodes['motorrad_store_view'],
            'is_active' => 1
        ],
        [
            'identifier' => 'youdrive_online_booking_disclaimer',
            'title' => 'YouDrive Online Booking Disclaimer BMW',
            'content' => '<p><strong>Please note: </strong>The vehicle image shown reflects the base specification 
                of the vehicle you have requested to test drive. The vehicle you test drive may be a different model 
                year, different colour and have optional features that differ from the specification displayed.</p>',
            'stores' => $storeCodes['bmw_store_view'],
            'is_active' => 1
        ],
        [
            'identifier' => 'youdrive_online_booking_disclaimer',
            'title' => 'YouDrive Online Booking Disclaimer MINI',
            'content' => '<p><strong>Please note: </strong>The vehicle image shown reflects the base specification of 
                the vehicle you have requested to test drive. The vehicle you test drive may be a different model year, 
                different colour and have optional features that differ from the specification displayed.</p>',
            'stores' => $storeCodes['mini_store_view'],
            'is_active' => 1
        ],
        [
            'identifier' => 'youdrive_online_booking_disclaimer',
            'title' => 'YouDrive Online Booking Disclaimer MOTORRAD',
            'content' => '<p><strong>Please note: </strong>The motorcycle image shown reflects the base specification 
                of the motorcycle you have requested to test ride. The motorcycle you test ride may be a different 
                year, different colour and have optional features that differ from the specification displayed.</p>',
            'stores' => $storeCodes['motorrad_store_view'],
            'is_active' => 1
        ],
        [
            'identifier' => 'youdrive_book_another',
            'title' => 'YouDrive Book Another BMW',
            'content' => 'Would you like to book another test drive?',
            'stores' => $storeCodes['bmw_store_view'],
            'is_active' => 1
        ],
        [
            'identifier' => 'youdrive_book_another',
            'title' => 'YouDrive Book Another MINI',
            'content' => 'Keen to test drive another MINI?',
            'stores' => $storeCodes['mini_store_view'],
            'is_active' => 1
        ],
        [
            'identifier' => 'youdrive_book_another',
            'title' => 'YouDrive Book Another MOTORRAD',
            'content' => 'Would you like to book another test ride?',
            'stores' => $storeCodes['motorrad_store_view'],
            'is_active' => 1
        ],
        [
            'identifier' => 'youdrive_next_step_statement',
            'title' => 'YouDrive Next Step Statement MINI',
            'content' => '<p>Can\'t decide on one MINI? You can book another test drive and compare the driving fun.</p>',
            'stores' => $storeCodes['mini_store_view'],
            'is_active' => 1
        ],
        [
            'identifier' => 'youdrive_next_step_statement',
            'title' => 'YouDrive Next Step Statement MOTORRAD',
            'content' => '<p>Having trouble deciding which BMW Motorcycle to choose? You can book another test ride 
                and compare the feeling.</p>',
            'stores' => $storeCodes['motorrad_store_view'],
            'is_active' => 1
        ]
    ];

    //add new blocks and edit some
    foreach ($cmsBlocks as $blockData) {
            Mage::getModel('cms/block')
                ->setStoreId($blockData['stores'])
                ->load($blockData['identifier'], 'identifier')
                ->addData($blockData)
                ->save();
    }
} catch (Exception $e) {
    Mage::logException($e);
}

$this->endSetup();

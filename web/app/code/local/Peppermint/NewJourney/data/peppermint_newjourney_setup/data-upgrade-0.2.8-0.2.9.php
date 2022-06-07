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

$cmsBlocks =  [
    [
        'identifier' => 'youdrive_howitworks',
        'title' => 'YouDrive How It Works BMW',
        'stores' => 'bmw_store_view',
        'is_active' => 1,
        'content' => <<<EOF
<strong>How It Works:</strong>
<p>Book your BMW test drive online in 4 simple steps.</p>
<ul>
    <li>Choose your vehicle</li>
    <li>Select a location</li>
    <li>Pick a convenient date and time</li>
    <li>Confirm your booking</li>
</ul>
<p>If your model of interest isn't available online, you can still send an enquiry to your BMW Retailer to arrange the experience.</p>
EOF
    ],
    [
        'identifier' => 'youdrive_tncs',
        'title' => 'YouDrive Ts&Cs BMW',
        'stores' => 'bmw_store_view',
        'is_active' => 1,
        'content' => <<<EOF
<strong>Terms and Conditions:</strong>
<p>Before you make your booking, please take note of the following</p>
<ul>
    <li>Valid Driver's License or Learner's license required</li>
    <li>You must be accompanied by an employee of the retailer</li>
    <li>Test Drive experience may not exceed 1 hour</li>
</ul>
EOF
    ],
];

foreach ($cmsBlocks as $cmsBlock) {
    $block = Mage::getModel('cms/block')->getCollection()
        ->addFieldToFilter('identifier', $cmsBlock['identifier'])
        ->addStoreFilter($bmwStoreId, false)
        ->getFirstItem();

    if ($block->getId()) {
        $block->setContent($cmsBlock['content'])
            ->setStores([$bmwStoreId])
            ->save();
    } else {
        $block = Mage::getModel('cms/block');
        $block->setTitle($cmsBlock['title']);
        $block->setIdentifier($cmsBlock['identifier']);
        $block->setStores([$bmwStoreId]);
        $block->setIsActive(1);
        $block->setContent($cmsBlock['content']);
        $block->save();
    }
}

$installer->endSetup();

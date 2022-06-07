<?php
/**
 * @category  Peppermint
 * @package   Peppermint_NewJourneyMini
 * @author    Mariam Khelashvili <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */

$installer = $this;
$installer->startSetup();
$miniStoreId = Mage::getModel('core/store')->load('mini_store_view', 'code')->getId();

$blockValue = <<<EOF
<p>Your estimated future trade-in value</p>
<hr>
<ul>
    <li><span>%28%</span><span>in 4 weeks</span></li>
    <li><span>%56%</span><span>in 8 weeks</span></li>
    <li><span>%84%</span><span>in 12 weeks</span></li>
    <li><span>%112%</span><span>in 16 weeks</span></li>
    <li><span>%140%</span><span>in 20 weeks</span></li>
</ul>
EOF;

$installer->setConfigData(Rockar_FutureValue_Helper_Data::XML_FUTURE_VALUE_BLOCK, $blockValue, 'stores' , $miniStoreId);

if (!Mage::getModel('cms/block')->setStoreId($miniStoreId)->load('est_value_disclaimer')->getId()) {
    $block = Mage::getModel('cms/block');
    $block->setTitle('Estimated Value Disclaimer');
    $block->setIdentifier('est_value_disclaimer');
    $block->setStores([$miniStoreId]);
    $block->setIsActive(1);
    $block->setContent(<<<EOF
<p>*The "Trade-In Value" of your vehicle is an estimate provided and many factors that cannot be assessed without a physical inspection of the vehicle may affect actual value. The final or actual "Trade-In Value" for your vehicle will be confirmed upon physical inspection of your vehicle having been undertaken by an authorised and approved MINI retailer.</p>
EOF
    );
    $block->save();
}

$installer->endSetup();
<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FutureValue
 * @author    Ausma Smite <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$bmwWebsiteId = Mage::getModel('core/website')->load('bmw')->getId();
$bmwStoreId = Mage::app()->getWebsite($bmwWebsiteId)
    ->getDefaultGroup()
    ->getDefaultStoreId();

$blockValue = '
<p>Your estimated future trade-in value</p>
<hr>
<ul>
    <li><span>%28%</span><span>in 4 weeks</span></li>
    <li><span>%56%</span><span>in 8 weeks</span></li>
    <li><span>%84%</span><span>in 12 weeks</span></li>
    <li><span>%112%</span><span>in 16 weeks</span></li>
    <li><span>%140%</span><span>in 20 weeks</span></li>
</ul>';

$installer->setConfigData(Rockar_FutureValue_Helper_Data::XML_FUTURE_VALUE_BLOCK, $blockValue, 'stores', $bmwStoreId);

$installer->endSetup();

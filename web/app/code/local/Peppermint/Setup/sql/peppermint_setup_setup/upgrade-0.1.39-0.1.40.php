<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Setup
 * @author    Taras Kapushchak <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();

$website = Mage::getModel('core/website')->load('bmw', 'code');
$websiteId = $website->getId();
$coreConfigDataFallback = '[current]:[current]' . PHP_EOL . '[current]:default2';

$this->setConfigData(
    'design/fallback/fallback',
    $coreConfigDataFallback,
    Mage_Adminhtml_Block_System_Config_Form::SCOPE_WEBSITES,
    $websiteId
);

$installer->endSetup();

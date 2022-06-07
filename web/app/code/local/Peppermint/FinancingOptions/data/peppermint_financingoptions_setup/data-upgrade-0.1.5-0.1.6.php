<?php

/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Lucaci Stefan <lucacistefan.alexandru@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

/** Set balloon slider data */
/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
// Prepare baloon data
$balloonData = [0, 10, 20, 30, 40, 50, 60];
$balloonString = implode(',', $balloonData);
// Save the balloon data to config
$installer->setConfigData('finance_overlay/balloon/slider_steps', $balloonString);
$installer->setConfigData('finance_overlay/balloon/slider_default', 40);

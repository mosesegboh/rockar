<?php
/**
 * @category Peppermint
 * @package Peppermint_Customer
 * @author Chris Plant <christopher.plant@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

/** @var Mage_Core_Model_resource_Setup $this */
$installer = $this;
$installer->startSetup();

// Set customer field configuration
$installer->setConfigData('customer/address/prefix_options', 'Mr.;Ms.;Mrs.;Miss;Dr.');

$installer->endSetup();

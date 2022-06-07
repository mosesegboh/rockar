<?php
/**
 * @category Peppermint
 * @package Peppermint_Customer
 * @author Adrian Pescar <adrian.pescar@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

/** @var Mage_Core_Model_Resource_Setup $this */
$installer = $this;

// Set customer field configuration
$installer->setConfigData('customer/address/prefix_options', 'Mr.;Ms.;Mrs.;Miss.;Dr.');

<?php
/**
 * Populate initial snapshot for vin pricing report
 *
 * @category  Peppermint
 * @package   Peppermint\Shell
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

require_once dirname(__FILE__) . '/../../../app/Mage.php';

Mage::app();
Mage::helper('peppermint_reports')->fillVinPricingSnapshot();

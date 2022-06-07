<?php
/**
 * Script that triggers MQ import for products.
 *
 * @category  Peppermint
 * @package   Peppermint_Shell
 * @author    Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

require_once dirname(__FILE__) . '/../../app/Mage.php';

Mage::app();
Mage::getModel('peppermint_importer/aggregator')->doAggregate();

<?php

/**
 * @category  Setup
 * @package   Peppermint_Setup
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->setConfigData('order_amend/part_exchange/allow_manual_price', 1);

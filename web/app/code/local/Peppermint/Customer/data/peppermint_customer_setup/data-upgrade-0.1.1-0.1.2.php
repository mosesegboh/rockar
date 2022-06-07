<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Customer
 * @author    Ana-Maria Buliga <anamaria.buliga@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();

// Set sort order value for region customer attribute
$installer->updateAttribute('customer_address', 'region', 'sort_order', 75);

$installer->endSetup();

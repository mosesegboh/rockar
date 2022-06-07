<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Orderstatus
 * @author    Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

/** @var Mage_Sales_Model_Entity_Setup $installer */
$installer = $this;
$installer->startSetup();
$installer->setConfigData('order_amend/order_status/order_can_be_amended', 'vehicle_reserved,order_placed');
$installer->endSetup();

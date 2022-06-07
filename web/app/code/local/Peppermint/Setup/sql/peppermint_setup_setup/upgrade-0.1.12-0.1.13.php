<?php

/**
 * @category     Peppermint
 * @package      Peppermint_Setup
 * @author       Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();
// Set sales email order confirmation disabled
$coreConfigSalesEmailOrderConfirmation = ['value' => 0];
// Set Partial payment deposit to 0
$coreConfigPartialPaymentGeneralDeposit = ['value' => 0];

$table = $this->getTable('core_config_data');
$connection = $installer->getConnection();
$connection->update($table, $coreConfigPartialPaymentGeneralDeposit, 'path="partial_payment/general/deposit"');
$connection->update($table, $coreConfigSalesEmailOrderConfirmation, 'path="sales_email/order/enabled"');

$installer->endSetup();

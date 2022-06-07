<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Checkout
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

/** @var $this Mage_Core_Model_Resource_Setup */
$installer = new Mage_Sales_Model_Mysql4_Setup('sales_setup');
$connection = $installer->getConnection();

/* Add column to sales_flat_quote table */
$tableName = $installer->getTable('sales/quote');

$otpPopupPassed = array(
    'type' => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
    'comment' => 'Otp popup passed',
);

if ($connection->tableColumnExists($tableName, 'otp_popup_passed')) {
    $connection->dropColumn($tableName, 'otp_popup_passed');
}

$installer->addAttribute('quote', 'otp_popup_passed', $otpPopupPassed);

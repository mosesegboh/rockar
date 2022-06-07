<?php
/**
 * @category     Peppermint
 * @package      Peppermint_Orderstatus
 * @author       Krists Dadzitis <techteam@rockar.com>
 * @copyright    Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

/** @var Mage_Sales_Model_Entity_Setup $installer */
$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();

$connection->insert(
    $installer->getTable('sales/order_status'),
    [
        'status' => Peppermint_Sales_Model_Order::STATUS_AMENDMENT_CANCELLED,
        'label' => 'Amendment Cancelled'
    ]
);

$connection->insert(
    $installer->getTable('sales/order_status_state'),
    [
        'status' => Peppermint_Sales_Model_Order::STATUS_AMENDMENT_CANCELLED,
        'state' => Peppermint_Sales_Model_Order::STATE_CANCELED,
        'is_default' => 0
    ]
);

$installer->endSetup();
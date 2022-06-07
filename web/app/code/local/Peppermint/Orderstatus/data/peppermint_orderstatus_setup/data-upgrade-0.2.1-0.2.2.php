<?php
/**
 * @category     Peppermint
 * @package      Peppermint_Orderstatus
 * @author       Bobur Zaitov <techteam@rockar.com>
 * @copyright    Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

/** @var Mage_Sales_Model_Entity_Setup $installer */
$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();

$connection->insert(
    $installer->getTable('peppermint_orderstatus/status_mapping'),
    [
        'orderstatus_id' => 2,
        'order_status' => 'invoice_submitted'
    ]
);

$connection->insert(
    $installer->getTable('sales/order_status'),
    [
        'status' => 'invoice_submitted',
        'label' => 'Invoice submitted'
    ]
);

$connection->insert(
    $installer->getTable('sales/order_status_state'),
    [
        'status' => 'invoice_submitted',
        'state' => 'processing',
        'is_default' => 0
    ]
);

$installer->endSetup();

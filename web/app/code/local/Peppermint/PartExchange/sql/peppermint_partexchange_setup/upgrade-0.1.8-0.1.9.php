<?php
/**
 * @category     Peppermint
 * @package      Peppermint_PartExchange
 * @author       Ketevani Revazishvili <techteam@rockar.com>
 * @copyright    Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
/** @var $connection Varien_Db_Adapter_Interface*/
$connection = $installer->getConnection();
$installer->startSetup();
$table = $installer->getTable('rockar_partexchange/order');

$connection->addColumn(
    $table,
    'bidding_id',
    [
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 255,
        'nullable'  => true,
        'default'   => null,
        'comment'   => 'Order Bidding id from trade in'
    ]
);

$installer->endSetup();

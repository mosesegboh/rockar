<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

/** @var $this Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$orderTable = $installer->getTable('sales/order');
$installer->getConnection()
    ->addColumn(
        $orderTable,
        'completed_on',
        [
            'type' => Varien_Db_Ddl_Table::TYPE_DATETIME,
            'nullable' => true,
            'default' => null,
            'comment' => 'Order Completed Date'
        ]
    );

$installer->endSetup();

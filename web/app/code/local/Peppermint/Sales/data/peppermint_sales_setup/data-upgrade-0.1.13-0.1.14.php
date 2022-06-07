<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();
$orderTable = $this->getTable('sales/order');
$orderStatusTable = $this->getTable('sales/order_status_history');
/** @var Varien_Db_Adapter_Interface $connection */
$connection = $installer->getConnection();

$subSelect = clone $connection->select();
$subSelect->from(
    ['h' => $installer->getTable('sales/order_status_history')],
    [
        'parent_id',
        'created_date' => new Zend_Db_Expr("MIN(created_at)")
    ]
)
    ->group('parent_id')
    ->where('status = ?', Peppermint_Sales_Model_Order::STATUS_ORDER_COMPLETE);

$select = $connection->select()
    ->join(
        ['soh' => $subSelect],
        'o.entity_id = soh.parent_id',
        ['completed_on' => 'soh.created_date']
    );
$query = $select->crossUpdateFromSelect(['o' => $installer->getTable('sales/order')]);
$connection->query($query);

$installer->endSetup();

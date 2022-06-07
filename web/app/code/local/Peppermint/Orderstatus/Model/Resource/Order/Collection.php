<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Orderstatus
 * @author    Jiraphong witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Orderstatus_Model_Resource_Order_Collection extends Rockar_Orderstatus_Model_Resource_Order_Collection
{
    /**
     * Add order filter
     *
     * @param Mage_Sales_Model_Order|int $order
     * @return Peppermint_Orderstatus_Model_Resource_Order_Collection
     */
    public function addOrderFilter($order)
    {
        if ($order instanceof Mage_Sales_Model_Order) {
            $order = $order->getId();
        }

        $this->addFilter('order_id', $order);
        $this->setOrder('created_at', Varien_Data_Collection_Db::SORT_ORDER_DESC);

        // Rewrite here | for peppermint we have additional order status mapping on observer
        // Sorting by time does not always grab the latest status especially when observer run one after the other
        $this->setOrder($this->getResource()->getIdFieldName(), Varien_Data_Collection_Db::SORT_ORDER_DESC);

        return $this;
    }
}
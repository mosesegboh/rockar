<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021  Rockar, Ltd (https://rockar.com)
 */

class Peppermint_Sales_Model_Resource_Report_ProductOrderPricing_Collection extends Rockar_Sales_Model_Resource_Order_Grid_Collection
{
    /**
     * Get SQL for get record count
     *
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();
        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);
        $countSelect->reset(Zend_Db_Select::GROUP);
        $countSelect->columns(['count' => 'COUNT(distinct main_table.entity_id)']);

        return $countSelect;
    }

    /**
     * Adding item to item array
     * Overridden to don't throw duplication exception, as data here can't be manipulated
     * just skip the duplicated record instead
     *
     * @param Varien_Object $item
     * @return Varien_Data_Collection
     */
    public function addItem(Varien_Object $item)
    {
        $itemId = $this->_getItemId($item);

        if (!is_null($itemId)) {
            if (!isset($this->_items[$itemId])) {
                $this->_items[$itemId] = $item;
            }
        } else {
            $this->_addItem($item);
        }

        return $this;
    }

    /**
     * Get collection size
     *
     * @return integer
     */
    public function getSize()
    {
        if (is_null($this->_totalRecords)) {
            $sql = $this->getSelectCountSql();
            $this->_totalRecords = $this->getConnection()->fetchOne($sql, $this->_bindParams);
        }

        return intval($this->_totalRecords);
    }
}

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Jiraphong witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Sales_Model_Resource_Sap_Fail extends Mage_Core_Model_Resource_Db_Abstract
{
    protected $_isPkAutoIncrement = false;

    /**
     * init model
     */
    protected function _construct()
    {
        $this->_init('peppermint_sales/sap_fail', 'order_id');
    }

    /**
     * Insert/Update peppermint_sales_sap_fail table
     *
     * @param array $data
     * @return Peppermint_Sales_Model_Resource_Sap_fail
     */
    public function insertEntry(array $data)
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->insertOnDuplicate(
            $this->getMainTable(),
            $data
        );

        return $this;
    }

    /**
     * Delete from peppermint_sales_sap_fail table
     *
     * @param array $data
     * @return Peppermint_Sales_Model_Resource_Sap_fail
     */
    public function deleteEntry(array $data)
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->delete(
            $this->getMainTable(),
            ['order_id = ?' => $data['order_id']]
        );

        return $this;
    }
}

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Importer
 * @author    Jiraphong witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Importer_Model_Resource_Product_Fail extends Mage_Core_Model_Resource_Db_Abstract
{
    protected $_isPkAutoIncrement = false;

    /**
     * init model
     */
    protected function _construct()
    {
        $this->_init('peppermint_importer/product_fail', 'vin');
    }

    /**
     * Insert/Update peppermint_importer_product_fail table
     *
     * @param array $data
     * @return Peppermint_Importer_Model_Resource_Product_fail
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
     * Delete from peppermint_importer_product_fail table
     *
     * @param array $data
     * @return Peppermint_Importer_Model_Resource_Product_fail
     */
    public function deleteEntry(array $data)
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->delete(
            $this->getMainTable(),
            ["{$this->_idFieldName} = ?" => $data['vin']]
        );

        return $this;
    }
}

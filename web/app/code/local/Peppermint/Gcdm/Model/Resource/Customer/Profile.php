<?php
/**
 * @category     Peppermint
 * @package      Peppermint_Gcdm
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Gcdm_Model_Resource_Customer_Profile extends Mage_Core_Model_Resource_Db_Abstract
{
    protected $_isPkAutoIncrement = false;

    protected function _construct()
    {
        $this->_init('peppermint_gcdm/customer_profile', 'customer_id');
    }

    /**
     * Insert/Update peppermint_gcdm_customer_profile table
     *
     * @param array $data
     * @return Peppermint_Gcdm_Model_Resource_Customer_Profile
     */
    public function sync(array $data)
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->insertOnDuplicate(
            $this->getMainTable(),
            $data
        );

        return $this;
    }
}

<?php

/**
 * @category     Peppermint
 * @package      Peppermint_Gcdm
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Gcdm_Model_Resource_Customer_Profile_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('peppermint_gcdm/customer_profile');
    }
}

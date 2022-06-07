<?php

/**
 * @category     Peppermint
 * @package      Peppermint_Gcdm
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Gcdm_Model_Customer_Profile extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('peppermint_gcdm/customer_profile');
    }
}

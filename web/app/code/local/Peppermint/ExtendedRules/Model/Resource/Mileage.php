<?php

/**
 * @category     Peppermint
 * @package      Peppermint\ExtendedRules
 * @author       Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_ExtendedRules_Model_Resource_Mileage extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Init resource
     */
    protected function _construct()
    {
        $this->_init('peppermint_extendedrules/mileage', 'id');
    }
}

<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Customer
 * @author       Catalin Lungu <catalin.lungu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Customer_Model_Entity_Attribute_Source_Country extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Get list of all available countries
     *
     * @return array|mixed
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = Mage::getModel('directory/country')->getResourceCollection()
                ->toOptionArray();
        }

        return $this->_options;
    }
}

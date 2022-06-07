<?php

/**
 * @category     Peppermint
 * @package      Peppermint_ExtendedRules
 * @author       Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_ExtendedRules_Block_Adminhtml_Rules_ExceptionCap_Edit extends Rockar_ExtendedRules_Block_Adminhtml_Rules_ExceptionCap_Edit
{
    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return string
     */
    public function getHeaderText()
    {
        return ($id = Mage::registry('current_rule')->getId()) ? 
            Mage::helper('rockar_extendedrules')->__("Edit MM Code Rule. (ID: %s)", $id) : 
            Mage::helper('rockar_extendedrules')->__('New MM Code Rule');
    }
}

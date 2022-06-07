<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Sales
 * @author       Catalin Lungu <catalin.lungu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Sales_Helper_Admin extends Mage_Core_Helper_Abstract
{
    /**
     * Check if the request is from frontend or backend
     * 
     * @return boolean true if the request was made fron backend side, false otherwise
     */
    public function isAdmin()
    {
        return (Mage::app()->getStore()->isAdmin() || (Mage::getDesign()->getArea() == 'adminhtml'));
    }
}

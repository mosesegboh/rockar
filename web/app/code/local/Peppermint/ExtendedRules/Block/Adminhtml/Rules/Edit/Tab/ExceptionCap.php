<?php

/**
 * @category     Peppermint
 * @package      Peppermint_ExtendedRules
 * @author       Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_ExtendedRules_Block_Adminhtml_Rules_Edit_Tab_ExceptionCap extends Rockar_ExtendedRules_Block_Adminhtml_Rules_Edit_Tab_ExceptionCap
{
    /**
     * Change headers for grid table
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        $helper = Mage::helper('rockar_extendedrules');
        $this->getColumn('cap_id')->setHeader($helper->__('MM Code'));
        $this->getColumn('year')->setHeader($helper->__('Year of Registration'));

        return $this;
    }
}

<?php

/**
 * @category     Peppermint
 * @package      Peppermint\ExtendedRules
 * @author       Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_ExtendedRules_Model_Mileage extends Mage_Core_Model_Abstract
{
    protected $_eventPrefix = 'peppermint_extendedrules_mileage';

    /**
     * Init resource
     */
    protected function _construct()
    {
        $this->_init('peppermint_extendedrules/mileage');
    }

    /**
     * Added price rounding for deduction value
     */
    protected function _beforeSave()
    {
        $deductionColumn = 'deduction_value';
        $data = Mage::app()->getRequest()->getPost();

        if (!empty($data) && isset($data[$deductionColumn])) {
            $data[$deductionColumn] = Mage::getModel('core/store')->roundPrice($data[$deductionColumn]);
            $this->setData($data);
        }

        parent::_beforeSave();
    }
}

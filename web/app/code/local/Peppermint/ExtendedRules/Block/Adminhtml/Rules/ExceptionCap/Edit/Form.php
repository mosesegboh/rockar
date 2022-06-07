<?php

/**
 * @category     Peppermint
 * @package      Peppermint_ExtendedRules
 * @author       Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_ExtendedRules_Block_Adminhtml_Rules_ExceptionCap_Edit_Form extends Rockar_ExtendedRules_Block_Adminhtml_Rules_ExceptionCap_Edit_Form
{
    /**
     * Change labels
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $form = $this->getForm();
        $helperDataExRules = Mage::helper('rockar_extendedrules');
        $mmCodeLabel = $helperDataExRules->__('MM Code');
        $yearCodeLabel = $helperDataExRules->__('Year of Registration');
        $form->getElement('cap_id')
            ->setLabel($mmCodeLabel)
            ->setTitle($mmCodeLabel);
        $form->getElement('year')
            ->setLabel($yearCodeLabel)
            ->setTitle($yearCodeLabel);

        return $this;
    }
}

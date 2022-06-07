<?php

/**
 * @category     Peppermint
 * @package      Peppermint\ExtendedRules
 * @author       Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_ExtendedRules_Block_Adminhtml_Rules_Mileage_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare Form and add elements to form
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        /* @var $model Peppermint_ExtendedRules_Model_Mileage */
        $model = Mage::registry('current_rule');
        $helper = Mage::helper('rockar_extendedrules');
        $form = new Varien_Data_Form([
            'method' => 'post',
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', ['id' => $this->getRequest()->getParam('id')])
        ]);
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => $helper->__('General Information')]);
        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }
        $fieldset->addField('mileage_from', 'text', [
            'name' => 'mileage_from',
            'label' => $helper->__('Mileage from'),
            'title' => $helper->__('Mileage from'),
            'class' => 'validate-number',
            'required' => true
        ]);
        $fieldset->addField('mileage_to', 'text', [
            'name' => 'mileage_to',
            'label' => $helper->__('Mileage to'),
            'title' => $helper->__('Mileage to'),
            'class' => 'validate-number',
            'required' => true
        ]);
        $fieldset->addField('deduction_value', 'text', [
            'name' => 'deduction_value',
            'label' => $helper->__('Deduction value'),
            'title' => $helper->__('Deduction value'),
            'note' => $helper->__('Use negative number (-50) to deduct the value. Positive (50) will increase it.'),
            'class' => 'validate-number',
            'required' => true
        ]);
        $fieldset->addField('deduction_type', 'select', [
            'name' => 'deduction_type',
            'label' => $helper->__('Deduction Type'),
            'title' => $helper->__('Deduction Type'),
            'required' => true,
            'options' => Mage::getModel('rockar_partexchange/adminhtml_system_config_source_deductionType')->toArray()
        ]);
        $form->setValues($model->getData())
            ->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}

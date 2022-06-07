<?php
/**
 * @category  Peppermint
 * @package   Peppermint_ShortfallAllowance
 * @author    Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

class Peppermint_ShortfallAllowance_Block_Adminhtml_Shortfall_Allowance_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $model = Mage::registry('peppermint_shortfall_allowance');

        $form = new Varien_Data_Form([
            'method' => 'post',
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save')
        ]);

        $fieldset = $form->addFieldset(
            'general_fieldset', 
            ['legend' => $this->__('General Information')]
        );

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        $fieldset->addField(
            'models', 
            'multiselect', 
            [
                'name' => 'models',
                'label' => $this->__('Models'),
                'title' => $this->__('Models'),
                'required' => true,
                'values' => Mage::getModel('peppermint_shortfallallowance/adminhtml_system_config_source_attribute_options')
                    ->toOptionArray(Mage::helper('peppermint_shortfallallowance')->getModelAttribute())
            ]
        );

        $fieldset->addField(
            'shortfall_limit', 
            'text', 
            [
                'name' => 'shortfall_limit',
                'label' => $this->__('Shortfall Limit'),
                'title' => $this->__('Shortfall Limit'),
                'required' => true,
                'class' => 'validate-not-negative-number'
            ]
        );

        Mage::dispatchEvent('peppermint_shortfallallowance_default_shortfall_allowance_form', 
            [
                'form' => $form, 
                'model' => $model
            ]
        );

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}

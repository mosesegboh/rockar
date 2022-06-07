<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Catalog
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Catalog_Block_Adminhtml_ModelMatrix_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $model = Mage::registry('peppermint_model_matrix_mapping');

        $form = new Varien_Data_Form([
            'method' => 'post',
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save')
        ]);

        $fieldset = $form->addFieldset(
            'general_fieldset', 
            ['legend' => $this->__('General Information')]
        );

        if ($model->getModelCarousel()) {
            $fieldset->addField('model_carousel', 'hidden', ['name' => 'model_carousel']);
        }

        $fieldset->addField(
            'position',
            'text', 
            [
                'name' => 'position',
                'label' => $this->__('Position'),
                'title' => $this->__('Position'),
                'required' => true,
                'class' => 'validate-not-negative-number'
            ]
        );

        Mage::dispatchEvent('peppermint_modelMatrix_edit_form',
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

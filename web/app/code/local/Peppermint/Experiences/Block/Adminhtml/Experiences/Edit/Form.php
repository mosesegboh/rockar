<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Experiences
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Experiences_Block_Adminhtml_Experiences_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $model = Mage::registry('peppermint_experiences');

        $form = new Varien_Data_Form([
            'method' => 'post',
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save'),
            'enctype' => 'multipart/form-data'
        ]);

        $fieldset = $form->addFieldset(
            'general_fieldset',
            ['legend' => $this->__('General Information')]
        );

        if ($model->getId()) {
            $fieldset->addField('experience_id', 'hidden', ['name' => 'experience_id']);
        }

        $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => $this->__('Experience Name'),
                'title' => $this->__('Experience Name'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'label',
            'text',
            [
                'name' => 'label',
                'label' => $this->__('Experience Label'),
                'title' => $this->__('Experience Label'),
                'class' => 'validate-length maximum-length-60',
                'required' => true,
                'maxlength' => 60
            ]
        );

        $fieldset->addField(
            'textblock',
            'textarea',
            [
                'name' => 'textblock',
                'label' => $this->__('Text block'),
                'title' => $this->__('Text block'),
                'class' => 'validate-length maximum-length-150',
                'required' => true,
                'note' => $this->__('To add variables use syntax {{var variable_code}}. Predefined variables are:<br> 
                    `product_title`<br>`product_short_title`<br>`product_subtitle`<br>`product_short_subtitle`'
                )
            ]
        );

        $fieldset->addField(
            'link_label',
            'text',
            [
                'name' => 'link_label',
                'label' => $this->__('HTML Link Label'),
                'title' => $this->__('HTML Link Label'),
                'class' => 'validate-length maximum-length-255',
                'required' => true
            ]
        );

        $fieldset->addField(
            'link_url',
            'text',
            [
                'name' => 'link_url',
                'label' => $this->__('HTML Target URL'),
                'title' => $this->__('HTML Target URL'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'image',
            'image',
            [
                'name' => 'image',
                'class' => 'required-entry required-file',
                'label' => $this->__('Image'),
                'title' => $this->__('Image'),
                'required' => true
            ]
        );

        Mage::dispatchEvent('peppermint_experiences_default_experiences_form',
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

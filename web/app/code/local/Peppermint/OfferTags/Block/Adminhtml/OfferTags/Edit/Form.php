<?php
/**
 * @category  Peppermint
 * @package   Peppermint_OfferTags
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_OfferTags_Block_Adminhtml_OfferTags_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $model = Mage::registry('peppermint_offertags');

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
            $fieldset->addField('offertag_id', 'hidden', ['name' => 'offertag_id']);
        }

        $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => $this->__('Item Name'),
                'title' => $this->__('Item Name'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'action_type',
            'select',
            [
                'name' => 'action_type',
                'label' => $this->__('Action Type'),
                'title' => $this->__('Action Type'),
                'required' => true,
                'options' => [
                    Peppermint_OfferTags_Helper_Data::DISPLAY_OPTION_ICON => $this->__('Icon'),
                    Peppermint_OfferTags_Helper_Data::DISPLAY_OPTION_TEXT => $this->__('Text'),
                    Peppermint_OfferTags_Helper_Data::DISPLAY_OPTION_ICON_TEXT => $this->__('Icon and Text')
                ]
            ]
        );

        $fieldset->addField('brand_bg_color',
            'checkbox',
            [
                'name' => 'brand_bg_color',
                'label' => $this->__('Brand Background Color'),
                'checked' => $model ? $model->getBrandBgColor() : false,
                'value' => 1
            ]);

        $fieldset->addField(
            'label',
            'text',
            [
                'name' => 'label',
                'label' => $this->__('Offer Tag Label'),
                'title' => $this->__('Offer Tag Label'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'icon',
            'image',
            [
                'name' => 'icon',
                'class' => 'required-entry required-file',
                'label' => $this->__('Icon Image'),
                'title' => $this->__('Icon Image'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'sort_order',
            'text',
            [
                'name' => 'sort_order',
                'label' => $this->__('Sort Order'),
                'title' => $this->__('Sort Order '),
                'required' => false,
                'class' => 'validate-not-negative-number'
            ]
        );

        Mage::dispatchEvent('peppermint_offertags_default_offertags_form',
            [
                'form' => $form,
                'model' => $model
            ]
        );

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
            ->addFieldMap('action_type', 'action_type')
            ->addFieldMap('brand_bg_color', 'brand_bg_color')
            ->addFieldMap('label', 'label')
            ->addFieldMap('icon', 'icon')
            ->addFieldDependence(
                'brand_bg_color',
                'action_type',
                'text'
            )
            ->addFieldDependence(
                'icon',
                'action_type',
                [
                    Peppermint_OfferTags_Helper_Data::DISPLAY_OPTION_ICON,
                    Peppermint_OfferTags_Helper_Data::DISPLAY_OPTION_ICON_TEXT
                ]
            )
        );

        return parent::_prepareForm();
    }
}

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_ProductPods
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_ProductPods_Block_Adminhtml_Items_Edit_Form extends Rockar_ProductPods_Block_Adminhtml_Items_Edit_Form
{
    /**
     * Owerwrite to add Label field
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();

        /* @var $model Rockar_ProductPods_Model_Item */
        $model = Mage::registry('current_pods_item');

        $form = $this->getForm();
        $fieldset = $form->getElement('base_fieldset');
        $fieldset->addField(
            'label',
            'text',
            [
                'name' => 'Label',
                'label' => $this->__('Label'),
                'title' => $this->__('Label'),
                'required' => false,
                'value' => $model->getData('label'),
            ],
            'text_template'
        );

        return $this;
    }
}

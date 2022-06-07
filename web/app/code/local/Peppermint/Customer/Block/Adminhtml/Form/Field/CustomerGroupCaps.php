<?php
/**
 * @category     Peppermint
 * @package      Peppermint_Customer
 * @author       Ketevani Revazishvili <techteam@rockar.com>
 * @copyright    Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Customer_Block_Adminhtml_Form_Field_CustomerGroupCaps extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    protected $_customerGroupsRenderer;

    /**
     * {@inheritdoc}
     */
    protected function _prepareToRender()
    {
        $this->addColumn('customer_group', [
            'label' => $this->__('Customer Group'),
            'renderer' => $this->_getCustomerGroupsFieldRenderer(),
            'style' => 'width:100px;',
            'class' => 'customer_group'
        ]);

        $this->addColumn('individual_cap', [
            'label' => $this->__('Individual Order Cap'),
            'style' => 'width:100px;',
            'class' => 'validate-digits'
        ]);

        $this->addColumn('corporate_cap', [
            'label' => $this->__('Corporate Order Cap'),
            'style' => 'width:100px;',
            'class' => 'validate-digits'
        ]);

        $this->setExtraParams('style="max-height: 20em; overflow-y: auto; padding-right: 2em;"');

        $this->_addAfter = true;
    }

    /**
     * Returns customer groups field renderer block
     *
     * @return Rockar_Customer_Block_Adminhtml_Form_Field_CustomerGroups
     */
    protected function _getCustomerGroupsFieldRenderer()
    {
        if (!$this->_customerGroupsRenderer) {
            $layout = $this->getLayout();

            $this->_customerGroupsRenderer = $layout->createBlock('rockar_customer/adminhtml_form_field_customerGroups',
                '', [
                    'is_render_to_js_template' => true
                ]);
        }

        return $this->_customerGroupsRenderer;
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareArrayRow(Varien_Object $row)
    {
        $customerGroupData = $this->_getCustomerGroupsFieldRenderer()->calcOptionHash($row->getData('customer_group'));
        $row->setData('option_extra_attr_' . $customerGroupData, 'selected="selected"');
    }
}

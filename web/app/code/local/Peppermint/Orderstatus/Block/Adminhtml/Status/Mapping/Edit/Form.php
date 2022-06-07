<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Orderstatus
 * @author    Jiraphong witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Orderstatus_Block_Adminhtml_Status_Mapping_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Getter for current model
     *
     * @return null|Peppermint_Orderstatus_Model_Status_Mapping
     */
    protected function _getModel()
    {
        return Mage::registry('current_orderstatus');
    }

    /**
     * Getter for module title
     *
     * @return string
     */
    protected function _getModelTitle()
    {
        return $this->__('Status Mapping');
    }

    /**
     * Prepare form
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();

        $form = new Varien_Data_Form(['id' => 'edit_form',
            'action' => $this->getData('action'),
            'method' => 'post'
        ]);

        $model = $this->_getModel();
        $modelTitle = $this->_getModelTitle();

        $form = new Varien_Data_Form([
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/save'),
            'method'    => 'post'
        ]);

        $fieldset = $form->addFieldset('base_fieldset', [
            'legend'    => $this->__("$modelTitle Information"),
            'class'     => 'fieldset-wide'
        ]);

        if ($model && $model->getId()) {
            $modelPk = $model->getResource()->getIdFieldName();

            $fieldset->addField($modelPk, 'hidden', ['name' => $modelPk]);
        }

        $fieldset->addField('order_status', 'select', [
            'label'     => $this->__('Order status'),
            'title'     => $this->__('Order status'),
            'name'      => 'order_status',
            'required'  => false,
            'values'   => Mage::getResourceModel('sales/order_status_collection')->toOptionHash()
        ]);

        $fieldset->addField('orderstatus_id', 'select', [
            'label'     => $this->__('Rockar Order status'),
            'title'     => $this->__('Rockar Order status'),
            'name'      => 'orderstatus_id',
            'required'  => false,
            'values'   => Mage::getModel('rockar_orderstatus/adminhtml_system_config_source_customerStatus')->toOptionArray()
        ]);

        if ($model) {
            $form->setValues($model->getData());
        }

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}

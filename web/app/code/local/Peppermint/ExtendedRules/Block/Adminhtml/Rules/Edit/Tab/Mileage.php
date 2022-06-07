<?php

/**
 * @category     Peppermint
 * @package      Peppermint\ExtendedRules
 * @author       Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_ExtendedRules_Block_Adminhtml_Rules_Edit_Tab_Mileage extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('mileageRulesGrid');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->extendedRulesHelper = Mage::helper('rockar_extendedrules');
    }

    /**
     * Adding new button 'Add New Rule'
     *
     * @return string
     */
    public function getMainButtonsHtml()
    {
        $html = parent::getMainButtonsHtml();
        $html .= $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData([
                'label' => $this->extendedRulesHelper->__('Add New Mileage'),
                'onclick' => "setLocation('" . $this->getUrl('*/extendedrules_mileage/new') . "')",
            ])->toHtml();

        return $html;
    }

    /**
     * Prepare collection for grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        /* @var $collection Peppermint_ExtendedRules_Model_Resource_Mileage_Collection */
        $collection = Mage::getModel('peppermint_extendedrules/mileage')->getResourceCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns
     *
     * @return $this
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('id', [
            'header' => $this->extendedRulesHelper->__('ID'),
            'index' => 'id',
            'width' => '50px'
        ]);
        $this->addColumn('mileage_from', [
            'header' => $this->extendedRulesHelper->__('Mileage From'),
            'index' => 'mileage_from',
            'type' => 'number'
        ]);
        $this->addColumn('mileage_to', [
            'header' => $this->extendedRulesHelper->__('Mileage To'),
            'index' => 'mileage_to',
            'type' => 'number'
        ]);
        $this->addColumn('deduction_value', [
            'header' => $this->extendedRulesHelper->__('Deduction'),
            'index' => 'deduction_value',
            'type' => 'number'
        ]);
        $this->addColumn('deduction_type', [
            'header' => $this->extendedRulesHelper->__('Deduction Type'),
            'index' => 'deduction_type',
            'type' => 'options',
            'options' => Mage::getModel('rockar_partexchange/adminhtml_system_config_source_deductionType')->toArray()
        ]);

        return parent::_prepareColumns();
    }

    /**
     * Return row url
     *
     * @param $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/extendedrules_mileage/edit', [
            'id' => $row->getId(),
        ]);
    }

    /**
     * Return grid filter url
     * 
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/extendedrules_mileage/grid');
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id')
            ->getMassactionBlock()
            ->setFormFieldName('mileage_rule_id')
            ->addItem('delete', [
                'label' => $this->extendedRulesHelper->__('Delete'),
                'url'  => $this->getUrl('*/extendedrules_mileage/massDelete'),
                'confirm' => $this->extendedRulesHelper->__('Are you sure?')
            ]);

        return $this;
    }
}

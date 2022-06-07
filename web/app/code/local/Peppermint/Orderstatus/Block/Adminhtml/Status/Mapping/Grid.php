<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Orderstatus
 * @author    Jiraphong witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Orderstatus_Block_Adminhtml_Status_Mapping_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setId('grid_id')
            ->setDefaultSort('position')
            ->setDefaultDir('asc')
            ->setSaveParametersInSession(true);
    }

    /**
     * Prepare collection
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $this->setCollection(Mage::getModel('peppermint_orderstatus/status_mapping')->getCollection());

        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $helper = Mage::helper('peppermint_orderstatus');

        $this->addColumn('orderstatus_id', [
            'header'  => $helper->__('Rockar Order status'),
            'align'   => 'left',
            'index'   => 'orderstatus_id',
            'type'    => 'options',
            'options' => $helper->getRockarOrderStatusHashArray()
        ]);

        $this->addColumn('order_status', [
            'header' => $helper->__('Order status'),
            'align'  => 'left',
            'index'  => 'order_status'
        ]);

        return parent::_prepareColumns();
    }

    /**
     * Getter for row url
     *
     * @param object $row
     * @return string
     */
    public function getRowUrl($row)
    {
       return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }

    /**
     * Prepare mass action
     *
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField(Mage::getModel('rockar_orderstatus/status')->getResource()->getIdFieldName());
        $this->getMassactionBlock()->setFormFieldName('ids');

        $this->getMassactionBlock()->addItem('delete', [
             'label' => $this->__('Delete'),
             'url' => $this->getUrl('*/*/massDelete'),
        ]);

        return $this;
    }
}

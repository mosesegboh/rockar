<?php
/**
 * @category    Peppermint
 * @package     Peppermint\Orderamend
 * @author      Sergejs Plisko <techteam@rockar.com>
 * @copyright   Copyright (c) 2020 Rockar, Ltd (https://rockar.com)
 */

class Peppermint_Orderamend_Block_Adminhtml_Amend_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setDefaultSort('id');
        $this->setId('peppermint_orderamend_amend_grid');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepare collection
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $orderGridTable = Mage::getSingleton('core/resource')->getTableName('sales/order_grid');
        $collection = Mage::getResourceModel('sales/order_collection');
        $collection->getSelect()
            ->joinLeft(
                ['order_grid' => $orderGridTable],
                'main_table.increment_id = order_grid.increment_id',
                ['base_grand_total']
            )
            ->where('order_grid.increment_id IS NULL');

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare table columns
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'real_order_id', [
                'header' => Mage::helper('sales')->__('Order #'),
                'width' => '80px',
                'type' => 'text',
                'index' => 'increment_id'
            ]
        );

        $this->addColumn(
            'created_at', [
                'header' => Mage::helper('sales')->__('Purchased On'),
                'index' => 'created_at',
                'type' => 'datetime',
                'width' => '100px'
            ]
        );

        $this->addColumn(
            'status', [
                'header' => Mage::helper('sales')->__('Status'),
                'index' => 'status',
                'type' => 'options',
                'width' => '70px',
                'options' => Mage::getSingleton('sales/order_config')->getStatuses()
            ]
        );

        $this->addColumn(
            'action',
            [
                'header' => Mage::helper('sales')->__('Action'),
                'width' => '50px',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => Mage::helper('sales')->__('View'),
                        'url' => array('base' => '*/sales_order/view'),
                        'field' => 'order_id',
                        'data-column' => 'action'
                    ]
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true
            ]
        );

        return parent::_prepareColumns();
    }
}

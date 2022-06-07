<?php
/**
 * @category  Peppermint
 * @package   Peppermint\OrderCap
 * @author    Lika Sikharulia <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_OrderCap_Block_Adminhtml_OrderCap_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        parent::_construct();

        $this->setId('ordercapGrid');
        $this->setDefaultSort('code');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        $localStores = Mage::helper('peppermint_ordercap')->getAdminUserLocalStores(true);
        $collection = Mage::getModel('rockar_localstores/stores')->getCollection()
            ->addFieldToFilter('status', ['eq' => 1]);

        if ($localStores) {
            $collection->addFieldToFilter(
                'code',
                ['in' => $localStores]
            );
        }
        
        Mage::dispatchEvent(
            'peppermint_orderCap_adminhtml_orderCap_grid_prepare_collection',
            ['collection' => $collection]
        );
        
        $this->setCollection($collection);
        
        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn('code', [
            'header' => $this->__('Store Name'),
            'align' => 'left',
            'index' => 'code',
            'renderer' => 'Peppermint_OrderCap_Block_Adminhtml_OrderCap_Renderer_Name',
            'filter_condition_callback' => [$this, '_filterStoreName']
        ]);

        $this->addColumn('brands', [
            'header' => $this->__('Brands'),
            'align' => 'left',
            'index' => 'brands',
            'filter' => false,
            'sortable' => false,
            'renderer' => 'Peppermint_OrderCap_Block_Adminhtml_OrderCap_Renderer_Brand'
        ]);

        $this->addColumn('active_orders', [
            'header' => $this->__('Number Of Active Orders'),
            'align' => 'left',
            'filter' => false,
            'sortable' => false,
            'renderer' => 'Peppermint_OrderCap_Block_Adminhtml_OrderCap_Renderer_ActiveOrders'
        ]);

        $this->addColumn('active_order_cap', [
            'header' => $this->__('Active Order Cap'),
            'align' => 'left',
            'filter' => false,
            'sortable' => false,
            'renderer' => 'Peppermint_OrderCap_Block_Adminhtml_OrderCap_Renderer_ActiveOrderCap'
        ]);

        $this->addColumn('status', [
            'header' => $this->__('Status'),
            'align' => 'left',
            'filter' => false,
            'sortable' => false,
            'renderer' => 'Peppermint_OrderCap_Block_Adminhtml_OrderCap_Renderer_Status'
        ]);

        Mage::dispatchEvent(
            'peppermint_ordercap_adminhtml_ordercap_grid_prepare_collection',
            ['block' => $this]
        );

        return parent::_prepareColumns();
    }

    /**
     * {@inheritdoc}
     *
     * @return Peppermint_OrderCap_Model_Resource_Stores_Collection|Varien_Data_Collection
     */
    public function getCollection()
    {
        return parent::getCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoadCollection()
    {
        foreach ($this->getCollection() as $item) {
            if (is_string($item->getData('store_view'))) {
                $item->setData('store_view', explode(',', $item->getData('store_view')));
            }
        }

        return $this;
    }

    /**
     * Filter stores by name or code
     *
     * @param $collection
     * @param $column
     * @return $this
     */
    protected function _filterStoreName($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if (!$value) {
            return $this;
        }

        $collection->addFieldToFilter(
            ['name', 'code'],
            [
                ['like' => '%' . $value . '%'],
                ['like' => '%' . $value . '%']
            ]
        );

        return $this;
    }
}
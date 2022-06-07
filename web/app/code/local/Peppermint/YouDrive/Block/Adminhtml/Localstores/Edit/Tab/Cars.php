<?php
/**
 * @category  Peppermint
 * @package   Peppermint_YouDrive
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_YouDrive_Block_Adminhtml_Localstores_Edit_Tab_Cars
    extends Rockar_YouDrive_Block_Adminhtml_Localstores_Edit_Tab_Cars
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Prepare collection for grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid|void
     * @throws Exception
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('rockar_youdrive/vehicle')->getCollection();
        $collection->addProductData();
        $localStore = Mage::getModel('rockar_localstores/stores')->load($this->getRequest()->getParam('id'));
        $collection->addFieldToFilter('assigned_to', $localStore->getCode());
        Mage::dispatchEvent('rockar_youdrive_vehicle_grid_prepare_collection', ['collection' => $collection]);
        $this->setCollection($collection);
        Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumn('assigned_to', [
            'header' => $this->__('Assigned To'),
            'index' => 'assigned_to',
            'type' => 'options',
            'options' => Mage::getModel('rockar_localstores/adminhtml_system_config_source_stores')->toCodesArray(),
            'filter_index' => 'assigned_to',
            'filter'    => false,
            'sortable'  => false
        ]);
    }
}

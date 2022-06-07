<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Sykander Gul <sykander.gul@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Sales_Block_Adminhtml_Sales_Order_View_Tab_Documents extends Mage_Adminhtml_Block_Widget_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Rockar_Customer_Block_Adminhtml_Customer_Edit_Tab_Download constructor.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('peppermint_sales_documents_grid');
        $this->setDefaultSort('name');
        $this->setDefaultDir(Varien_Data_Collection::SORT_ORDER_DESC);
        $this->setUseAjax(false);
    }

    /**
     * Prepare collection
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('peppermint_sales/order_document')->getCollection();
        $collection->addFieldToFilter(
            'order_id',
            $this->getOrder()
                ->getId()
        );
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare columns for grid
     *
     * @return void
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        // OTP ID
        $this->addColumn(
            'file_param',
            [
                'header'    => $this->__('OTP #'),
                'width'     => '100',
                'index'     => 'file_param',
                'type'      => 'text',
                'filter'    => null,
                'sortable'  => true
            ]
        );

        // DOCUMENT NAME
        $this->addColumn(
            'name',
            [
                'header'    => $this->__('Document Name'),
                'width'     => '100',
                'index'     => 'name',
                'type'      => 'text',
                'filter'    => null,
                'sortable'  => true
            ]
        );

        $this->addColumn(
            'created_at',
            [
                'header'    => $this->__('Created At'),
                'width'     => '100',
                'index'     => 'created_at',
                'type'      => 'datetime',
                'format'    => 'dd/MM/Y hh:mm:ss a',
                'filter'    => null,
                'sortable'  => true
            ]
        );

        $this->addColumn(
            'download',
            [
                'header'    => $this->__('Download'),
                'width'     => '100',
                'index'     => 'file_helper',
                'type'      => 'action',
                'filter'    => false,
                'sortable'  => false,
                'getter'    => 'getId',
                'actions'   => [
                    [
                        'caption'   => $this->__('Download'),
                        'url'       => ['base' => 'adminhtml/sales_order_documents/download'],
                        'field'     => 'id'
                    ]
                ]
            ]
        );
    }

    /**
     * Get order to display
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::registry('current_order')
            ?: new Varien_Object();
    }

    /**
     * Row click url
     *
     * @param $row
     * @return string|false
     */
    public function getRowUrl($row)
    {
        return false;
    }

    /**
     * Rerieve grid URL
     *
     * @return string|false
     */
    public function getGridUrl()
    {
        return false;
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('OTP Documents');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('OTP Documents');
    }

    /**
     * Can show tab in tabs
     *
     * @return bool
     */
    public function canShowTab()
    {
        return Mage::helper('peppermint_sales/order')->isAdminUserAllowedToDownloadOtp();
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function canDisplayContainer()
    {
        return true;
    }
}

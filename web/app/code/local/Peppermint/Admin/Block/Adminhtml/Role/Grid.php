<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Admin
 * @author    Ana-Maria Buliga <anamaria.buliga@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Admin_Block_Adminhtml_Role_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    private $_adminHelper;

    /**
     * Peppermint_Admin_Block_Adminhtml_Role_Grid constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->_adminHelper = Mage::helper('peppermint_admin');
        parent::__construct();
        $this->setId('roleGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Prepare grid collection object.
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('peppermint_admin/role')->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns.
     *
     * @throws Exception
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => $this->_adminHelper->__('ID'),
                'align' => 'right',
                'width' => '50px',
                'type' => 'number',
                'index' => 'id'
            ]
        );

        $this->addColumn(
            'role',
            [
                'header' => $this->_adminHelper->__('Role'),
                'index' => 'role',
                'type' => 'text'
            ]
        );

        $this->addColumn(
            'client_id',
            [
                'header' => $this->_adminHelper->__('Client ID'),
                'index' => 'client_id',
                'type' => 'text'
            ]
        );

        $this->addColumn(
            'client_secret',
            [
                'header' => $this->_adminHelper->__('Client Secret'),
                'index' => 'client_secret',
                'type' => 'text'
            ]
        );

        $this->addColumn(
            'realm',
            [
                'header' => $this->_adminHelper->__('Realm Path'),
                'index' => 'realm',
                'type' => 'text'
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => $this->_adminHelper->__('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => [
                    '1' => $this->_adminHelper->__('Enabled'),
                    '0' => $this->_adminHelper->__('Disabled')
                ]
            ]
        );

        $this->addColumn(
            'action',
            [
                'header' => $this->_adminHelper->__('Action'),
                'width' => '100',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => $this->_adminHelper->__('Edit'),
                        'url' => ['base' => '*/*/edit'],
                        'field' => 'id'
                    ]
                ],
                'filter' => false,
                'is_system' => true,
                'sortable' => false
            ]
        );

        $this->addExportType('*/*/exportCsv', $this->_adminHelper->__('CSV'));
        $this->addExportType('*/*/exportExcel', $this->_adminHelper->__('Excel'));
        $this->addExportType('*/*/exportXml', $this->_adminHelper->__('XML'));

        return parent::_prepareColumns();
    }

    /**
     * Prepare grid massaction.
     *
     * @return $this|Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('role');
        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => $this->_adminHelper->__('Delete'),
                'url' => $this->getUrl('*/*/massDelete'),
                'confirm' => $this->_adminHelper->__('Are you sure?')
            ]
        );

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => $this->_adminHelper->__('Change status'),
                'url' => $this->getUrl('*/*/massStatus', ['_current' => true]),
                'additional' => [
                    'status' => [
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => $this->_adminHelper->__('Status'),
                        'values' => [
                            '1' => $this->_adminHelper->__('Enabled'),
                            '0' => $this->_adminHelper->__('Disabled')
                        ]
                    ]
                ]
            ]
        );

        return $this;
    }

    /**
     * @param $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');

        return parent::_afterLoadCollection();
    }
}

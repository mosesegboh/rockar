<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Customer
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Customer_Block_Adminhtml_Customer_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Peppermint_Customer_Block_Adminhtml_CustomersGrid constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('assignGroup');
        $this->setUseAjax(true);
        $this->setDefaultSort('entity_id');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepare collection
     *
     * @return Peppermint_Customer_Block_Adminhtml_CustomersGrid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('customer/customer_collection')->addNameToSelect()
            ->addAttributeToSelect([
                'email',
                'created_at',
                'group_id',
                'south_african_document_type',
                'south_african_id_number'
            ]);
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare Grid columns
     *
     * @return Peppermint_Customer_Block_Adminhtml_CustomersGrid
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $helper = Mage::helper('customer');

        $this->addColumn(
            'entity_id',
            [
                'header' => $helper->__('ID'),
                'width' => '50px',
                'index' => 'entity_id',
                'type' => 'number'
            ]
        );

        $this->addColumn(
            'name',
            [
                'header' => $helper->__('Name'),
                'index' => 'name'
            ]
        );

        $this->addColumn(
            'email',
            [
                'header' => $helper->__('Email'),
                'width' => '150',
                'index' => 'email',
                'renderer' => 'peppermint_customer/adminhtml_customer_grid_renderer_email'
            ]
        );

        $identificationTypes = Mage::helper('peppermint_customer')->getIdentificationTypes();

        $this->addColumn(
            'south_african_document_type',
            [
                'header' => $helper->__('Identification Type'),
                'width' => '100',
                'index' => 'south_african_document_type',
                'type' => 'options',
                'options' => $identificationTypes
            ]
        );

        $this->addColumn(
            'south_african_id_number',
            [
                'header' => $helper->__('Identification Number'),
                'index' => 'south_african_id_number',
                'renderer' => 'peppermint_customer/adminhtml_customer_grid_renderer_identification'
            ]
        );

        $groups = Mage::getResourceModel('customer/group_collection')->addFieldToFilter(
            'customer_group_id',
            ['gt' => 0]
        )
            ->load()
            ->toOptionHash();

        $this->addColumn(
            'group',
            [
                'header' => $helper->__('Group'),
                'width' => '100',
                'index' => 'group_id',
                'type' => 'options',
                'options' => $groups
            ]
        );

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn(
                'website_id',
                [
                    'header' => Mage::helper('customer')->__('Website'),
                    'align' => 'center',
                    'width' => '80px',
                    'type' => 'options',
                    'options' => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(true),
                    'index' => 'website_id'
                ]
            );
        }

        return parent::_prepareColumns();
    }

    /**
     * Return grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }

    /**
     * Prepare Mass Action
     *
     * @return $this|Peppermint_Customer_Block_Adminhtml_CustomersGrid
     */
    protected function _prepareMassaction()
    {
        $helper = Mage::helper('customer');
        $this->setMassactionIdField('entity_id');
        $groups = $this->helper('customer')->getGroups()->toOptionArray();
        array_unshift($groups, ['label' => '', 'value' => '']);

        $this->getMassactionBlock()
            ->setFormFieldName('customer')
            ->setUseSelectAll(false)
            ->setTemplate('peppermint/widget/grid/massaction.phtml')
            ->addItem(
                'assign_group',
                [
                    'label' => $helper->__('Assign a Customer Group'),
                    'url' => $this->getUrl('*/*/massAssignGroup'),
                    'additional' => [
                        'visibility' => [
                            'name' => 'group',
                            'type' => 'select',
                            'class' => 'required-entry',
                            'label' => $helper->__('Group'),
                            'values' => $groups
                        ]
                    ]
                ]
            );

        return $this;
    }
}

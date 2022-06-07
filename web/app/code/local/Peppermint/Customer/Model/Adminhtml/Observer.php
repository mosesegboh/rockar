<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Customer
 * @author    Jiraphong Witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Customer_Model_Adminhtml_Observer
{
    /**
     * @var Array
     */
    protected $_customAttributesToSelect;

    /**
     * Peppermint_Customer_Model_Adminhtml_Observer constructor.
     */
    public function __construct()
    {
        $this->_customAttributesToSelect = [
            'in_store_name',
            'dealer_id'
        ];
    }

    /**
     * Add 'Clear Customer GUID data' button to Admin Panel / Manage Customers / Edit Customer
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function addClearGuidBtn($observer)
    {
        $block = $observer->getEvent()->getBlock();
        $customer = $this->getCustomer();

        if ($block instanceof Mage_Adminhtml_Block_Customer_Edit && $customer
            && $customer->getId() && !$customer->isReadonly()
        ) {
            $block->addButton('clearCustomerGuid', [
                'label' => Mage::helper('peppermint_customer')->__('Clear Customer GUID data'),
                'onclick' => 'setLocation(\'' . $this->getCustomerGuidUrl() . '\')'
            ], 0);
        }
    }

    /**
     * Return current customer
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        return Mage::registry('current_customer');
    }

    /**
     * Return adminhtml url to clearGuidAction
     *
     * @return string
     */
    public function getCustomerGuidUrl()
    {
        return Mage::helper('adminhtml')->getUrl(
            '*/actions/clearGuid',
            ['customer_id' => $this->getCustomer()->getId()]
        );
    }

    /**
     * Add attribute to collection
     *
     * @param Varien_Event_Observer $observer
     * @return Peppermint_Customer_Model_Adminhtml_Observer
     */
    public function additionalAttributes(Varien_Event_Observer $observer)
    {
        $collection = $observer->getCollection();
        $collection->addAttributeToSelect($this->_customAttributesToSelect);

        return $this;
    }

    /**
     * Add custom columns to customer grid
     *
     * @param Varien_Event_Observer $observer
     * @return Peppermint_Customer_Model_Adminhtml_Observer
     */
    public function addCustomColumns(Varien_Event_Observer $observer)
    {
        /** @var Peppermint_Customer_Block_Adminhtml_Customer_Customer_Grid $customerGrid */
        $customerGrid = $observer->getCustomerGrid();
        $customerGrid->addColumnAfter(
            'in_store_name',
            [
                'header'    => Mage::helper('customer')->__('Created In (Local Store)'),
                'index'     => 'in_store_name',
                'type'      => 'options',
                'options'   => Mage::helper('peppermint_localstores/data')->getStoreOptions()
            ],
            'registered_in'
        );

        $customerGrid->addColumnAfter(
            'dealer_id',
            [
                'header'    => Mage::helper('customer')->__('UserID'),
                'index'     => 'dealer_id',
                'type'      => 'string'
            ],
            'in_store_name'
        );

        return $this;
    }
}

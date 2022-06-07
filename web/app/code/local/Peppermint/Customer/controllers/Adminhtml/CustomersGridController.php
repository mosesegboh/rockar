<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Customer
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Customer_Adminhtml_CustomersGridController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Customers list action
     */
    public function indexAction()
    {
        $this->_title($this->__('Assign a Customer Group'));
        $this->loadLayout();

        /**
         * Set active menu item
         */
        $this->_setActiveMenu('customer/assign_customer_group');

        /**
         * Append customers block to content
         */
        $this->_addContent(
            $this->getLayout()->createBlock('peppermint_customer/adminhtml_customer')
        );

        $this->renderLayout();
    }

    /**
     * Grid Action
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Assign customer group
     */
    public function massAssignGroupAction()
    {
        $customersIds = $this->getRequest()->getParam('customer');

        if (!is_array($customersIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select customer(s).'));
        } else {
            try {
                foreach ($customersIds as $customerId) {
                    $customer = Mage::getModel('customer/customer')->load($customerId);
                    $customer->setGroupId($this->getRequest()->getParam('group'));
                    $customer->save();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) were updated.', count($customersIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }


    /**
     * Check the permission to run it
     * @return mixed
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/assign_customer_group');
    }
}

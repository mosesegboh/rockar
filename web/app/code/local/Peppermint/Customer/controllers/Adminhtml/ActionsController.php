<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Customer
 * @author    Adrian Pescar <adrian.pescar@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

require_once Mage::getModuleDir('controllers', 'Rockar_Customer') . DS . 'Adminhtml' . DS . 'ActionsController.php';

class Peppermint_Customer_Adminhtml_ActionsController extends Rockar_Customer_Adminhtml_ActionsController
{
    /**
     * Check the permission to Manage Customers
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/manage');
    }

    /**
     * Clear customer GUID data
     *
     * @return void
     */
    public function clearGuidAction()
    {
        if ($customerId = $this->getRequest()->getParam('customer_id')) {
            try {
                Mage::helper('peppermint_customer')->clearCustomerGuidData($customerId);
                $this->_getSession()->addSuccess($this->__('Customers GUID data has been cleared.'));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($this->__('There is a problem clearing customer GUID data.'));
            }

            $this->_redirectReferer();
        }
    }
}

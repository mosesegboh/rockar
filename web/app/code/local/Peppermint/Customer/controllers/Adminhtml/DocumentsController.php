<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Customer
 * @author    Adrian Pescar <adrian.pescar@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

require_once Mage::getModuleDir('controllers', 'Rockar_Customer') . DS . 'Adminhtml' . DS . 'DocumentsController.php';

class Peppermint_Customer_Adminhtml_DocumentsController extends Rockar_Customer_Adminhtml_DocumentsController
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
}

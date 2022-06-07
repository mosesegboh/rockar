<?php
/**
 * @category    Peppermint
 * @package     Peppermint\Orderamend
 * @author      Sergejs Plisko <techteam@rockar.com>
 * @copyright   Copyright (c) 2020 Rockar, Ltd (https://rockar.com)
 */

class Peppermint_Orderamend_Adminhtml_AmendController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/amend');
    }

    /**
     * Incomplete Order Amendments index action
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_title($this->__('Incomplete Order Amendments'));
        $this->renderLayout();
    }
}

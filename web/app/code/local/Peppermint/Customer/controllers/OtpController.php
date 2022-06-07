<?php
/**
 * @category Peppermint
 * @package Peppermint_Customer
 * @author Lucaci Stefan <lucacistefan.alexandru@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Customer_OtpController extends Rockar_All_Controller_Front_Ajax
{
    /**
     * Set ajax check to false.
     *
     * @var boolean
     */
    protected $_checkAjax = false;

    /**
     * Check if customer is logged in.
     *
     * @return object
     */
    public function preDispatch()
    {
        parent::preDispatch();

        if (!$this->_getCustomerSession()->isLoggedIn()) {
            $this->_restrictDispatch();
        }

        return $this;
    }

    /**
     * Download OTP action.
     *
     * @return void
     */
    public function downloadAction()
    {
        $incrementId = $this->getRequest()->getParam('id');
        $order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);
        $file = Mage::helper('peppermint_checkout/pdf')->getOtpFilePath($incrementId);

        if ($order->getId() && file_exists($file)
            && $this->_getCustomerSession()->getCustomer()->getId() == $order->getCustomerId()
        ) {
            $this->_prepareDownloadResponse($incrementId . '.pdf', file_get_contents($file), 'application/pdf');
        } else {
            $this->getSession()->addError($this->__('You are not authorized to perform this action.'));
            $this->_redirectReferer();

            return;
        }
    }
}

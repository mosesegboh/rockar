<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Sykander Gul <sykander.gul@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Sales_Adminhtml_Sales_Order_DocumentsController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Is Allowed?
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::helper('peppermint_sales/order')->isAdminUserAllowedToDownloadOtp();
    }

    /**
     * Download OTP action.
     *
     * @return void
     */
    public function downloadAction(): void
    {
        try {
            $adminSession = Mage::getSingleton('adminhtml/session');

            $document = Mage::getModel('peppermint_sales/order_document')->load(
                $this->getRequest()
                    ->getParam('id')
            );

            $file = false;

            if ($document->getId()) {
                $helper = Mage::helper($document->getFileHelper());

                if (is_subclass_of($helper, Peppermint_Sales_Helper_Document_Interface)) {
                    $file = $helper->getDocument(
                        $document->getFileParam()
                    );
                }
            }

            // file can be empty string
            if ($file === false) {
                $adminSession->addError(
                    $this->__('Cannot find the document.')
                );

                return;
            }

            $this->_prepareDownloadResponse(
                $document->getName(),
                $file,
                'application/pdf'
            );

            $adminSession->addSuccess(
                $this->__('Document retrieved sucessfully.')
            );
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_redirectReferer();
        }
    }
}

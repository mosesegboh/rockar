<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Sales_Adminhtml_Report_ProductOrderPricingController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Applicable Rules index action
     */
    public function indexAction()
    {
        $this->_title($this->__('Product Order Pricing Report'));
        $this->loadLayout();

        /**
         * Set active menu item
         */
        $this->_setActiveMenu('report/pricing');

        /**
         * Append block to content
         */
        $this->_addContent(
            $this->getLayout()->createBlock('peppermint_sales/adminhtml_report_productOrderPricing')
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
     * Export csv action
     */
    public function exportCsvAction()
    {
        $fileName = 'product_order_pricing.csv';
        $content = $this->getLayout()->createBlock('peppermint_sales/adminhtml_report_productOrderPricing_grid')
            ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export excel action
     */
    public function exportExcelAction()
    {
        $fileName = 'product_order_pricing.xml';
        $content = $this->getLayout()->createBlock('peppermint_sales/adminhtml_report_productOrderPricing_grid')
            ->getExcelFile($fileName);
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Check the permission to run it
     *
     * @return mixed
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('report/pricing/product_order');
    }
}
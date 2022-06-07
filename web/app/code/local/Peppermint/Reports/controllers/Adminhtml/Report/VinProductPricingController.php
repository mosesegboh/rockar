<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Reports
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Reports_Adminhtml_Report_VinProductPricingController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Vin Product Pricing index action
     */
    public function indexAction()
    {
        $this->_title($this->__('Product VIN Pricing Report'));
        $this->loadLayout();

        /**
         * Set active menu item
         */
        $this->_setActiveMenu('report/pricing');

        /**
         * Append block to content
         */
        $this->_addContent(
            $this->getLayout()
                ->createBlock('peppermint_reports/adminhtml_vinProductPricing')
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
        $fileName = 'vin_product_pricing.csv';
        $content = $this->getLayout()
            ->createBlock('peppermint_reports/adminhtml_vinProductPricing_grid')
            ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export excel action
     */
    public function exportExcelAction()
    {
        $fileName = 'vin_product_pricing.xml';
        $content = $this->getLayout()
            ->createBlock('peppermint_reports/adminhtml_vinProductPricing_grid')
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
        return Mage::getSingleton('admin/session')->isAllowed('report/pricing/vin_product_pricing');
    }
}
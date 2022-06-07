<?php
/**
 * @category  Peppermint
 * @package   Peppermint_CatalogRule
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_CatalogRule_Adminhtml_Report_ApplicableRulesController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Applicable Rules index action
     */
    public function indexAction()
    {
        $this->_title($this->__('Products with Applicable Rules'));
        $this->loadLayout();

        /**
         * Set active menu item
         */
        $this->_setActiveMenu('report/pricing');

        /**
         * Append block to content
         */
        $this->_addContent(
            $this->getLayout()->createBlock('peppermint_catalogrule/adminhtml_report_applicableRules')
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
        $fileName = 'products_with_applicable_rules.csv';
        $content = $this->getLayout()->createBlock('peppermint_catalogrule/adminhtml_report_applicableRules_grid')
            ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export excel action
     */
    public function exportExcelAction()
    {
        $fileName = 'products_with_applicable_rules.xml';
        $content = $this->getLayout()->createBlock('peppermint_catalogrule/adminhtml_report_applicableRules_grid')
            ->getExcel($fileName);
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Check the permission to run it
     *
     * @return mixed
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('report/pricing/product_applicable_rules');
    }
}

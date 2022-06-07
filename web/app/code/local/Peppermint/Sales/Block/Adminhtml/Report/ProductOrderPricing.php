<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Sales_Block_Adminhtml_Report_ProductOrderPricing extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Peppermint_Sales_Block_Adminhtml_Report_ProductOrderPricing constructor.
     */
    public function __construct()
    {
        $this->_controller = 'adminhtml_report_productOrderPricing';
        $this->_blockGroup = 'peppermint_sales';
        $this->_headerText = $this->__('Product Order Pricing Report');
        parent::__construct();
        $this->_removeButton('add');
    }
}

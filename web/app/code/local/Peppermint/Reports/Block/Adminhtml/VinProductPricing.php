<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Reports
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Reports_Block_Adminhtml_VinProductPricing extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Peppermint_Reports_Block_Adminhtml_VinProductPricing constructor.
     */
    public function __construct()
    {
        $this->_controller = 'adminhtml_vinProductPricing';
        $this->_blockGroup = 'peppermint_reports';
        $this->_headerText = $this->__('Product VIN Pricing Report');
        parent::__construct();
        $this->_removeButton('add');
    }
}

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_CatalogRule
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_CatalogRule_Block_Adminhtml_Report_RulesLog extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Peppermint_CatalogRule_Block_Adminhtml_Report_RulesLog constructor.
     */
    public function __construct()
    {
        $this->_controller = 'adminhtml_report_rulesLog';
        $this->_blockGroup = 'peppermint_catalogrule';
        $this->_headerText = $this->__('Price Rule Audit');
        parent::__construct();
        $this->_removeButton('add');
    }
}

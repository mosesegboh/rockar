<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Jiraphong witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Sales_Model_Sap_Fail extends Mage_Core_Model_Abstract
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'peppermint_sales_sap_fail';

    /**
     * Init model
     */
    protected function _construct()
    {
        $this->_init('peppermint_sales/sap_fail');
    }
}

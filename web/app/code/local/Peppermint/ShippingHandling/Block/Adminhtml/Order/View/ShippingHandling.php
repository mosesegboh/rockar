<?php
/**
 * @category  Peppermint
 * @package   Peppermint_ShippingHandling
 * @author    Jiraphong witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_ShippingHandling_Block_Adminhtml_Order_View_ShippingHandling extends Rockar_ShippingHandling_Block_Adminhtml_Order_View_ShippingHandling
{
    /**
     * Check permissions
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        /**
         * Replacing Mage::getSingleton('admin/session')->isAllowed('rockar/shippinghandling')
         * since Order Amend now provides this functionlity
         */
        return false;
    }
}

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Orderamend
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Orderamend_Helper_Salesrule extends Rockar_Orderamend_Helper_Salesrule
{
    /**
     * Check if given coupon is the one that was saved on order placement
     * Overridden to make case-insensitive
     *
     * @param string $couponCode
     * @return bool
     */
    public function isSavedCoupon($couponCode)
    {
        $order = Mage::helper('rockar_orderamend')->getOrder();

        if (!($order && $order->getId())) {
            return false;
        }

        $orderAmendSalesRule = Mage::getModel('rockar_orderamend/salesRule_data')
            ->loadByOrderId($order->getId());

        return $orderAmendSalesRule
            && strtoupper($orderAmendSalesRule->getCouponCode()) === strtoupper($couponCode);
    }
}

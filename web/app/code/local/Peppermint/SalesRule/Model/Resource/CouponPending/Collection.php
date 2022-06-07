<?php
/**
 * @category  Peppermint
 * @package   Peppermint_SalesRule
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_SalesRule_Model_Resource_CouponPending_Collection extends Mage_SalesRule_Model_Resource_Coupon_Collection
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_init('peppermint_salesrule/couponPending');
    }
}

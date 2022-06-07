<?php
/**
 * @category  Peppermint
 * @package   Peppermint_PartialPayment
 * @author    Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_PartialPayment_Model_Observer extends Rockar_PartialPayment_Model_Observer
{
    /**
     * Calculate base total paid and total paid after placing order
     * Set status for an order to pending or processing (depends of px) seems that we use checkmo method.
     *
     * @param  Varien_Event_Observer $observer
     * @return $this
     */
    public function salesOrderPlaceAfter(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getData('order');
        Mage::getResourceModel('rockar_partialpayment/payment')->createPartialPayment($order);

        return $this;
    }
}

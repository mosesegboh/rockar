<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Orderamend
 * @author    Jiraphong Witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Orderamend_Model_Finance_Observer extends Rockar_Orderamend_Model_Finance_Observer
{
    /**
     * Override product data (pricing) before finance calculations
     * Rewrite of core function to use simple order item instead of configurable one
     *
     * @param Varien_Event_Observer $observer
     * Event: rockar_financing_options_get_quote_variables
     *
     * @return void
     */
    public function overrideProductData(Varien_Event_Observer $observer)
    {
        /** @var Rockar_Orderamend_Helper_Data $helper */
        $helper = Mage::helper('rockar_orderamend');

        if ($helper->isAmendmentPage()) {
            /** @var Mage_Catalog_Model_Product $product */
            $product = $observer->getProduct();

            /** @var Mage_Sales_Model_Order_Item $orderItem */
            $orderItem = $helper->getFirstSimpleOrderItem();

            $originalPrice = (float) $orderItem->getOriginalPrice();

            // Need to set the originalPrice corrrectly if the value is 0 when amending the initial order
            if ($originalPrice == 0) {
                $orderItem = $helper->getFirstVisibleOrderItem();
            }

            if ($orderItem) {
                $originalPrice = (float) $orderItem->getOriginalPrice();
                $basePrice = (float) $orderItem->getBasePrice();

                // some order item original price is set without rules applied, if so assign base price value as it has rules applied
                $originalPrice = $basePrice < $originalPrice ? $basePrice : $originalPrice;
            }

            if (Mage::helper('rockar_orderamend/order')->isOrderItemProduct($product, $orderItem)) {
                if (
                    Mage::helper('peppermint_orderamend/customerGroup')->isCustomerGroupChanged()
                    && $price = Mage::helper('peppermint_orderamend/customerGroup')->getCustomerGroupPrice()
                ) {
                    $product->setCustomPrice($price);
                } else {
                    if ($originalPrice > 0) {
                        // If it's the same product, use old order price instead of what's currently in catalog
                        $product->setCustomPrice($originalPrice);
                    }
                }
            }
        }
    }

    /**
     * Adjust data when converting quote item to order item
     *
     * @param Varien_Event_Observer $observer
     */
    public function adjustQuoteItemConvertToOrderItem(Varien_Event_Observer $observer)
    {
        $orderItem = $observer->getEvent()->getOrderItem();
        $customerGroupHelper = Mage::helper('peppermint_orderamend/customerGroup');

        /**
         * original price and base original price are missed during amendment creation,
         * these prices are default product prices without any discounts
         */
        $originalPrice = (float) Mage::helper('rockar_orderamend')->getFirstVisibleOrderItem()->getOriginalPrice();

        if (
            $customerGroupHelper->isCustomerGroupChanged()
            && $price = $customerGroupHelper->getCustomerGroupPrice()
        ) {
            $originalPrice = $price;
        }

        $orderItem->setOriginalPrice($originalPrice);
        $orderItem->setBaseOriginalPrice($originalPrice);
    }
}

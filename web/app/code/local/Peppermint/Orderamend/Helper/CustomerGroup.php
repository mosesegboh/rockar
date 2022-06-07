<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Orderamend
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Orderamend_Helper_CustomerGroup extends Mage_Core_Helper_Abstract
{
    /**
     * Check if customer group was changed comparing to original order
     *
     * @return boolean
     */
    public function isCustomerGroupChanged()
    {
        $helper = Mage::helper('rockar_orderamend');

        return (int) $helper->getOrder()->getCustomerGroupId() !== (int) $helper->getQuote()->getCustomerGroupId();
    }

    /**
     * Get price with discount for customer group in quote
     *
     * @return boolean
     */
    public function getCustomerGroupPrice()
    {
        $helper = Mage::helper('rockar_orderamend');
        $quote = $helper->getQuote();
        $model = Mage::getModel('rockar_orderamend/catalogRule_data')->load($helper->getOrder()->getId(), 'order_id');

        if (
            $model->getId()
            && $prices = $model->getAllCatalogRulePrices()
        ) {
            $pricesArray = Mage::helper('rockar_all')->jsonDecode($prices);

            foreach ($pricesArray as $priceItem) {
                if ((int) $priceItem['customer_group_id'] === (int) $quote->getCustomerGroupId()) {
                    return $priceItem['rule_price'];
                }
            }
        }

        return false;
    }
}

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_FinancingOptions_Helper_Checkout extends Rockar_FinancingOptions_Helper_Checkout
{
    /**
     * Return quote finance data.
     *
     * @param Mage_Sales_Model_Quote_Item       $quoteItem
     * @param Mage_Core_Controller_Request_Http $request
     *
     * @return array
     */
    public function getQuoteFinanceData($quoteItem = null, $request = null)
    {
        $helperAll = Mage::helper('rockar_all');

        if ($quoteItem === null) {
            $quoteItem = Mage::helper('rockar_checkout')->getQuoteItem();
        }
        $quoteFinanceData = $quoteItem->getData('finance_data');
        $quoteFinanceData = $quoteFinanceData ? $helperAll->jsonDecode($quoteFinanceData) : [];

        if (isset($quoteFinanceData['group_id'], $quoteFinanceData[$quoteFinanceData['group_id']])) {
            $quoteFinanceData[$quoteFinanceData['group_id']]['product_id'] = $quoteFinanceData['product_id'];
            $quoteFinanceData = $quoteFinanceData[$quoteFinanceData['group_id']];
        }

        $paramsData = $quoteFinanceData;

        if ($request !== null) {
            $paramsData = $request->getParams();
            $paramsData = array_filter($paramsData) ? array_merge($quoteFinanceData, $request->getParams()) : $quoteFinanceData;
        }

        $accessories = $quoteItem->getData('accessories');
        $accessories = $accessories ? $helperAll->jsonDecode($accessories) : [];

        $functionParams = Peppermint_FinancingOptions_Helper_Interfaces_QuoteData::prepareParams(
            isset($paramsData['product_id']) ? (int) $paramsData['product_id'] : (int) $quoteItem->getProductId(),
            $paramsData['mileage'] ?? 0,
            $paramsData['term'] ?? 0,
            $paramsData['deposit'] ?? 0,
            $paramsData['depositMultiple'] ?? 0,
            $paramsData['maintenance'] ?? 0,
            Rockar_FinancingOptions_Model_Calculation_Type_Abstract::CALC_TYPE_QUOTE,
            $accessories,
            $paramsData['payment_type'] ?? '',
            $paramsData['group_id'] ?? '',
            Mage::getSingleton('checkout/cart')->getQuote()->getCouponCode() ?: false,
            $paramsData['balloonPercentage'] ?? 0
        );

        return Mage::helper('financing_options')->getFinanceQuoteData($functionParams);
    }
}

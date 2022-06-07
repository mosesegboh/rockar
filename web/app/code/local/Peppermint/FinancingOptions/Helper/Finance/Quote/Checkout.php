<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_FinancingOptions_Helper_Finance_Quote_Checkout extends Rockar_FinancingOptions_Helper_Finance_Quote_Checkout
{
    /**
     * Return Finance Quote Data.
     *
     * @return array
     */
    public function getFinanceQuoteData()
    {
        $quoteItem = Mage::helper('rockar_checkout')->getQuoteItem();
        $product = Mage::helper('rockar_checkout/quote')->getFirstSimpleProduct(Mage::getSingleton('checkout/cart')->getQuote());
        $core = Mage::helper('rockar_all');

        $savedFinanceData = $core->jsonDecode($quoteItem->getFinanceData());

        $accessories = $quoteItem->getData('accessories');
        $accessories = $accessories ? $core->jsonDecode($accessories) : [];

        $financingData = Mage::helper('peppermint_financingoptions/finance_quote')->getFinanceQuoteData(
            $product,
            $savedFinanceData,
            $accessories,
            Rockar_FinancingOptions_Model_Calculation_Type_Abstract::CALC_TYPE_QUOTE
        );
        $financingData['payment_save_url'] = Mage::getUrl('checkout/onepage_financing/save', ['_secure' => true]);
        $financingData['progress_url'] = Mage::getUrl('checkout/onepage_financing/progress', ['_secure' => true]);
        $financingData['finance_url'] = Mage::getUrl('checkout/onepage_financing/options', ['_secure' => true]);
        $financingData['image'] = Mage::helper('peppermint_catalog/images')->getSmallImage($product);
        $financingData['image_alt'] = sprintf('%s %s', $product->getTitle(), $product->getSubtitle());

        return $financingData;
    }
}

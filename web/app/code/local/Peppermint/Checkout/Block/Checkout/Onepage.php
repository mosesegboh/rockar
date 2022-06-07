<?php
/**
 * @category  Pepermint
 * @package   Peppermint\Checkout
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020  Rockar, Ltd (https://rockar.com)
 */

class Peppermint_Checkout_Block_Checkout_Onepage extends Rockar2_Checkout_Block_Checkout_Onepage
{
    /**
     * Provide URL for the validate car request
     *
     * @return string
     */
    public function getValidateProductUrl()
    {
        return Mage::getUrl('checkout/onepage/validateQuoteItem');
    }

    /**
     * Provide URL for the car finder page
     *
     * @return string
     */
    public function getCarFinderUrl()
    {
        return Mage::helper('peppermint_checkout')->getCarFinderUrl();
    }

    /**
     * Returns product data array
     *
     * @param $product
     * @param $quoteItem
     *
     * @return array
     */
    public function getProductData($product, $quoteItem)
    {
        /** @var Rockar_FinancingOptions_Helper_Finance_Quote_Checkout $helper */
        $helper = Mage::helper('financing_options/finance_quote_checkout');
        $data = [];
        $currentCategory = Mage::helper('rockar_catalog')->getProductCategory($quoteItem->getProduct());

        $data['title'] = $product->getShortTitle();
        $data['subtitle'] = $product->getShortSubtitle();
        $data['category'] = $currentCategory;
        $data['sku'] = $quoteItem->getSku();
        $data['price'] = $helper->getFinanceQuoteData()['rockar_price'];
        $data['url'] = $product->getProductUrl();

        return $data;
    }
}

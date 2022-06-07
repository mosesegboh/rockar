<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Jiraphong Witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Checkout_Block_Checkout_Steps_Summary extends Rockar_Checkout_Block_Checkout_Steps_Summary
{
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
     * Gets the title for the product
     * Avoids duplication of the name in motorrad as the subtitle and Short title are either the same or null
     *
     * @return string
     */
    public function getProductTitle()
    {
        $product = $this->getQuoteItem()
            ->getProduct();

        return $product ? ($product->getShortTitle() . ' ' . $product->getShortSubtitle()) : parent::getProductName();
    }

    /**
     * Get product technical specification
     *
     * @return string
     */
    public function getTechnicalSpecificationsJson()
    {
        return Mage::helper('rockar_catalog/attributes')->getTechnicalSpecifications($this->_getProduct());
    }

    /**
     * Get product standard features
     *
     * @return string
     */
    public function getStandardFeatures()
    {
        return Mage::helper('rockar_catalog/attributes')->getStandardFeatures($this->_getProduct());
    }

    /**
     * Retrieve product
     *
     * @return Mage_Catalog_Model_Product
     */
    private function _getProduct()
    {
        $productId = Mage::helper('rockar_checkout')->getQuoteSimpleItemProduct()->getId();
        return Mage::getModel('catalog/product')->load($productId);
    }
}

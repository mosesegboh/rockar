<?php
/**
 * @category  Peppermint
 * @package   Peppermint_ApprovedUsed
 * @author    Krists Dadzitis <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_ApprovedUsed_Helper_Data extends Rockar_ApprovedUsed_Helper_Data
{
    /**
     * Rewrite of parent function to use singleton instead of loading a new model
     *
     * {@inheritDoc}
     */
    public function isApprovedUsedProduct($product, $storeId = null)
    {
        if (is_numeric($product)) {
            $product = Mage::getSingleton('catalog/product')->setId($product);
        }

        $categoryHelper = Mage::helper('rockar_approvedused/category');

        return in_array($categoryHelper->getApprovedUsedCategory($storeId), $product->getCategoryIds());
    }
}

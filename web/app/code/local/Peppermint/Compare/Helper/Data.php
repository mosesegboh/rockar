<?php
/**
 * @category    Peppermint
 * @package     Peppermint\Compare
 * @author      Andrian Kogoshvili <techteam@rockar.com>
 * @copyright   Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Compare_Helper_Data extends Rockar2_Compare_Helper_Data
{
    /**
     * Get Compare Data URL for the ajax action
     *
     * @return string
     */
    public function getCompareDataUrl()
    {
        return Mage::getUrl('rockar_compare/ajax/getcomparedata');
    }

    /**
     * Get if product is in compare list
     *
     * @param  $product
     * @return bool
     */
    public function getIsInCompareList($product) {
        $result = $this->_getConfigurableProduct($product);

        if ($result) {
            $result = Mage::helper('rockar_compare')->isInCompareList($result->getId());
        }

        return Mage::helper('core')->jsonEncode($result);
    }

    /**
     * Get Compare Add URL for the ajax action
     *
     * @param  $product
     * @return string | false
     */
    public function getCompareAddUrl($product)
    {
        $result = $this->_getConfigurableProduct($product);

        if ($result) {
            $result = Mage::helper('rockar_compare')->getAddUrl($result->getId());
        }

        return $result;
    }

    /**
     * Get Compare Remove URL for the ajax action
     *
     * @param  $product
     * @return string | false
     */
    public function getCompareRemoveUrl($product)
    {
        $result = $this->_getConfigurableProduct($product);

        if ($result) {
            $result = Mage::helper('rockar_compare')->getRemoveUrl($result);
        }

        return $result;
    }

    /**
     * Get configurable product from simple product
     *
     * @param  $product
     * @return mixed | false
     */
    private function _getConfigurableProduct($product) {
        $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($product->getId());

        if (isset($parentIds[0])) {
            return Mage::getModel('catalog/product')->load($parentIds[0]);
        } else {
            return false;
        }
    }

    /**
     * Get url for compare clear action
     *
     * @return string
     */
    public function getClearUrl()
    {
        return Mage::getUrl('rockar_compare/ajax/clear');
    }
}

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_ApprovedUsed
 * @author    Krists Dadzitis <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_ApprovedUsed_Helper_Finance extends Rockar_ApprovedUsed_Helper_Finance
{
    /**
     * Rewrite of parent function to use singleton instead
     * of loading a new model
     * and fix param id checking logic
     *
     * {@inheritDoc}
     */
    protected function _getCurrentCategoryId()
    {
        /** @var Mage_Core_Controller_Request_Http $request */
        $request = Mage::app()->getRequest();

        /** @var Mage_Catalog_Model_Category $category */
        $category = Mage::registry('current_category');

        if ($category) {
            return $category->getId();
        }

        $paramId = $request->getParam('id', false);

        if ($paramId && Mage::getModel('catalog/category')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($paramId)
            ->getEntityId())
        {
            return $paramId;
        } else {
            /** @var Rockar_ApprovedUsed_Helper_Data $helper */
            $helper = Mage::helper('rockar_approvedused');

            // Try to get product id param. It is passed through many different keys around the platform
            $productId = $helper->getProductIdParam($request);

            if ($productId) {
                $product = Mage::getSingleton('catalog/product')->setId($productId);

                if ($category = current($product->getCategoryIds())) {
                    return $category;
                }
            }

            if (Mage::getSingleton('catalog/session')->getLastViewedCategoryId()) {
                return (string) Mage::getSingleton('catalog/session')->getLastViewedCategoryId();
            }
        }

        return '0';
    }

    /**
     * Rewrite of parent function to use cache
     *
     * {@inheritDoc}
     */
    protected function _getFrontendFinanceType($categoryId)
    {
        return Mage::helper('peppermint_financingoptions/cache')->getFrontendFinanceType($categoryId);
    }

    /**
     * {@inheritDoc}
     */
    protected function _getBackendFinanceType()
    {
        /** @var Rockar_FinancingOptions_Model_Options $financeOption */
        $financeOption = Mage::registry('financing_option');

        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::registry('current_product');

        $financeType = $this->getFinanceProductTypeNew();

        if ($financeTypeId = Mage::registry($this->getRegistryCurrentFinanceType())) {
            $financeType = $financeTypeId;
        }

        /**
         * If in finance option edit view
         */
        if ($financeOption && $financeOption->getId()) {
            $financeTypes = explode(',', $financeOption->getFinanceType());

            foreach ($financeTypes as $type) {
                switch ((int)$type) {
                    case $this->getFinanceProductTypeApprovedUsed():
                        $financeType = $this->getFinanceProductTypeApprovedUsed();
                        break;
                    default:
                        break;
                }
            }
        }

        /**
         * If in product edit view
         */
        if ($product && $product->getId()) {
            $vehicleCondition = $product->getAttributeText('vehicle_condition');

            switch ($vehicleCondition) {
                case 'Used':
                case 'Demo':
                    $financeType = $this->getFinanceProductTypeApprovedUsed();
                    break;
                default:
                    break;
            }
        }

        return $financeType;
    }
}
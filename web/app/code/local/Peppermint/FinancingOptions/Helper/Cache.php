<?php
/**
 * Class to cache queries that are repeated.
 *
 * @category     Peppermint
 * @package      Peppermint_FinancingOptions
 * @author       Craig Goodspeed <techteam@rockar.com>
 * @copyright    Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_FinancingOptions_Helper_Cache extends Mage_Core_Helper_Abstract
{
    private $_calculationTypeCache = [];
    private $_financingOptionsCache = [];
    private $_frontendFinanceTypeCache = [];
    private $_parentIdsCache = [];
    private $_productCache = [];
    const PEPPERMINT_CACHE_MAGE_KEY = 'peppermint_financingoptions_cache_enabled';

    /**
     * Helper method to determine if the cache should be used or not.
     * @return bool
     */
    private function _shouldUseCache(): ?bool
    {
        return Mage::registry(self::PEPPERMINT_CACHE_MAGE_KEY);
    }

    /**
     * Interrogates the local cache for the payment type,
     * retrieves, stores and returns when not present,
     * returns when present
     *
     * @param String $paymentType
     * @return Peppermint_FinancingOptions_Model_Options
     */
    public function getType($paymentType)
    {
        if ($this->_shouldUseCache()) {
            if (!isset($this->_calculationTypeCache[$paymentType])) {
                $this->_calculationTypeCache[$paymentType] = Mage::getModel('rockar_financingoptions/options')->load($paymentType, 'type');
            }

            return $this->_calculationTypeCache[$paymentType];
        }

        return Mage::getModel('rockar_financingoptions/options')->load($paymentType, 'type');
    }

    /**
     * Compiles a key given the financialTermAlias, OptionId, Term and field
     * Interrogates the local cache for the compiled key,
     * retrieves, stores and returns when not present,
     * returns when present
     *
     * @param String $financeTermAlias
     * @param String $optionId
     * @param String $term
     * @param String $field
     *
     * @return float
     */
    public function getFinancingOptions($financeTermAlias, $optionId, $term, $field)
    {
        if ($this->_shouldUseCache()) {
            $key = $financeTermAlias . '/' . $optionId . '/' . $term;

            if (!isset($this->_financingOptionsCache[$key])) {
                $this->_financingOptionsCache[$key] = $this->_getFinancingOptions($financeTermAlias, $optionId, $term);
            }

            return ($this->_financingOptionsCache[$key])->getData($field);
        }

        return $this->_getFinancingOptions($financeTermAlias, $optionId, $term)->getData($field);
    }

    /**
     * Fetch the data from the database.
     *
     * @param String $financeTermAlias
     * @param String $optionId
     * @param String $term
     * @return mixed
     */
    private function _getFinancingOptions($financeTermAlias, $optionId, $term)
    {
        $collection = Mage::getModel($financeTermAlias)->getCollection()
            ->addFieldToFilter('option_id', $optionId)
            ->addFieldToFilter('term', $term);

        $collection->getSelect()
            ->limit(1);

        return $collection->getFirstItem();
    }

    /**
     * Get Frontend Finance Type from cache or database
     *
     * @param int $categoryId
     * @return mixed
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getFrontendFinanceType($categoryId)
    {
        $storeId = Mage::app()->getStore()->getId();

        if ($this->_shouldUseCache()) {
            $key = $storeId . '/' . $categoryId;

            if (!isset($this->_frontendFinanceTypeCache[$key])) {
                $this->_frontendFinanceTypeCache[$key] = $this->_getFrontendFinanceType($categoryId, $storeId);
            }

            return $this->_frontendFinanceTypeCache[$key];
        }

        return $this->_getFrontendFinanceType($categoryId, $storeId);
    }

    /**
     * Get Frontend Finance Type from database
     *
     * @param int $categoryId
     * @param int $storeId
     * @return mixed
     */
    private function _getFrontendFinanceType($categoryId, $storeId)
    {
        return Mage::getResourceModel('catalog/category')->getAttributeRawValue($categoryId, 'finance_type', $storeId)
            ?: Mage::helper('rockar_approvedused/finance')->getFinanceProductTypeNew();
    }

    /**
     * Get parent ids from cache or database
     *
     * @param int $productId
     * @return mixed
     */
    public function getParentIdsByChild($productId)
    {
        if ($this->_shouldUseCache()) {
            if (!isset($this->_parentIdsCache[$productId])) {
                $this->_parentIdsCache[$productId] = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($productId);
            }

            return $this->_parentIdsCache[$productId];
        }

        return Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($productId);
    }

    /**
     * Get a product from cache or database
     *
     * @param int $productId
     * @return false|Mage_Core_Model_Abstract|mixed
     */
    public function getProduct($productId)
    {
        if ($this->_shouldUseCache()) {
            if (!isset($this->_productCache[$productId])) {
                $this->_productCache[$productId] = Mage::getModel('catalog/product')->load($productId);
            }

            return $this->_productCache[$productId];
        }

        return Mage::getModel('catalog/product')->load($productId);
    }

    /**
     * clears the cached objects, so the data needs to be retrieved again.
     *
     * @return void
     */
    public function clear()
    {
        $this->_calculationTypeCache = [];
        $this->_financingOptionsCache = [];
        $this->_frontendFinanceTypeCache = [];
        $this->_parentIdsCache = [];
        $this->_productCache = [];
    }

    /**
     * enable the cache, adds an entry to the mage registry
     *
     * @throws Mage_Core_Exception
     * @return void
     */
    public function enableCache()
    {
        Mage::register(self::PEPPERMINT_CACHE_MAGE_KEY, true, true);
    }

    /**
     * Disable the use of the cache.
     *
     * @return void
     */
    public function disableCache()
    {
        Mage::unregister(self::PEPPERMINT_CACHE_MAGE_KEY);
    }

}

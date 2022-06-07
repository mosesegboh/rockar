<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Catalog
 * @author    Adrian Grigorita <adrian.grigorita@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Catalog_Helper_Collection_Sort extends Rockar2_Catalog_Helper_Collection_Sort
{
    /** @var Peppermint_FinancingOptions_Helper_Data */
    protected $_financingHelper;

    /**
     * Default sorting field (is calculated just before sorting).
     *
     * @return string
     */
    public function getDefaultSortField(): string
    {
        return 'final_price';
    }

    /**
     * Get sort field.
     * For cash: price
     * For lease/hire: monthly_price.
     *
     * @param boolean $addPrefix // If table prefix is specified, it will be added
     * @return string
     */
    public function getSortField($addPrefix = false): string
    {
        $financingHelper = $this->_getFinancingHelper();

        $defaultPayInFull = $financingHelper->getDefaultPayInFullPayment();
        $activePayment = $financingHelper->getActivePayment();

        if (in_array($activePayment['group_id'], array_column($defaultPayInFull, 'group_id'))) {
            // If cash
            $field = $this->getDefaultSortField();
        } else {
            // If lease/hire
            $field = $this->getDefaultSortFieldNoCash();
        }

        $prefix = $this->getSortFieldTable($field);

        // If prefix specified, then add it
        return ($prefix && $addPrefix) ? sprintf('%s.%s', $prefix, $field) : $field;
    }

    /**
     * Prepares collection sort data.
     * MUST be called before attempting sort.
     *
     * @param $collection
     * @return mixed
     */
    public function prepareCollectionForSort($collection)
    {
        $financingHelper = $this->_getFinancingHelper();

        // Only add sort_price field if it does not exist
        if (!$this->validateCollectionForSorting($collection)) {
            Mage::dispatchEvent(
                'rockar_catalog_collection_sort_prepare_before',
                ['collection' => $collection]
            );

            $defaultPayInFull = $financingHelper->getDefaultPayInFullPayment();
            $activePayment = $financingHelper->getActivePayment();

            if (in_array($activePayment['group_id'], array_column($defaultPayInFull, 'group_id'))) {
                // If cash
                $this->_addCashSortFieldExpression($collection);
            }

            Mage::dispatchEvent(
                'rockar_catalog_collection_sort_prepare_after',
                ['collection' => $collection]
            );
        }

        return $collection;
    }

    /**
     * Finance helper getter.
     *
     * @return Peppermint_FinancingOptions_Helper_Data
     */
    protected function _getFinancingHelper()
    {
        if (!$this->_financingHelper) {
            $this->_financingHelper = Mage::helper('financing_options');
        }

        return $this->_financingHelper;
    }
}

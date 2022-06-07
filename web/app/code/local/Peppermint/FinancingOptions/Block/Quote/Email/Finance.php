<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Jiraphong Witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_FinancingOptions_Block_Quote_Email_Finance extends Peppermint_FinancingOptions_Block_Order_Email_Finance
{
    // Manufacturer Support titles
    const BMW_MANUFACTURER_SUPPORT_TITLE = 'BMW Contribution';
    const MINI_MANUFACTURER_SUPPORT_TITLE = 'MINI Contribution';
    const MOTORRAD_MANUFACTURER_SUPPORT_TITLE = 'BMW Contribution';

    // Store codes
    const BMW_STORE = 'bmw_store_view';
    const MINI_STORE = 'mini_store_view';
    const MOTORRAD_STORE = 'motorrad_store_view';

    /**
     * @var Mage_Wishlist_Model_Item
     */
    protected $_wishListItem;

    /**
     * Retrieve Finance Variable from save wishlist
     * Adapted from Rockar_MySavedCars_Block_SavedCars::getFinanceQuoteData
     *
     * @return array
     */
    public function getFinanceVariables()
    {
        if ($this->_variables === null) {
            $helperAll = $this->helper('rockar_all');
            $wishListItem = $this->getWishListItem();

            $request = $wishListItem ? $wishListItem->getBuyRequest() : null;
            $tmpData = [];

            $productId = $request ? $request->getData('vehicleId') : null;
            if (!$request && !(int) $productId) {
                return $tmpData;
            }

            $product = Mage::getModel('catalog/product')->load((int) $productId);
            $fullFinanceData = $helperAll->jsonDecode($wishListItem->getData('financing_data'));
            $savedFinanceData = $fullFinanceData;

            // Fix for passing product_id data further to approved used functionality,
            // which determines if must switch finance tables to AU
            Mage::app()->getRequest()->setParam('product_id', $product->getId());

            $methodId = ($fullFinanceData['method'] ?? ($fullFinanceData['group_id'] ??  0));

            if (isset($fullFinanceData[$methodId])) {
                $savedFinanceData = array_merge($fullFinanceData[$methodId], array_filter($fullFinanceData, 'is_scalar'));
            }

            $accessories = Mage::helper('rockar_all')->jsonDecode($wishListItem->getData('accessories'));
            $product->setAccessories($accessories);

            if ($product) {
                $functionParams = Peppermint_FinancingOptions_Helper_Interfaces_QuoteData::prepareParams(
                    $product,
                    $savedFinanceData['mileage'] ?? 0,
                    $savedFinanceData['term'] ?? 0,
                    $savedFinanceData['deposit'] ?? 0,
                    $savedFinanceData['depositMultiple'] ?? 0,
                    $savedFinanceData['maintenance'] ?? 0,
                    Rockar_FinancingOptions_Model_Calculation_Type_Abstract::CALC_TYPE_WISHLIST,
                    $accessories,
                    $savedFinanceData['payment_type'] ?? '',
                    $savedFinanceData['group_id'] ?? '',
                    false,
                    $savedFinanceData['balloonPercentage'] ?? 0
                );

                $tmpData = $this->helper('financing_options')->getFinanceQuoteData($functionParams);

                $manufacturerSupport = $product->getPrice() - $product->getFinalPrice();

                if ($tmpData['finance_variables'] && $manufacturerSupport > 0) {
                    $storeCode = Mage::app()->getStore($product->getStoreId())->getCode();

                    switch ($storeCode) {
                        case self::BMW_STORE:
                            $title = self::BMW_MANUFACTURER_SUPPORT_TITLE;
                            break;
                        case self::MINI_STORE;
                            $title = self::MINI_MANUFACTURER_SUPPORT_TITLE;
                            break;
                        case self::MOTORRAD_STORE;
                            $title = self::MOTORRAD_MANUFACTURER_SUPPORT_TITLE;
                            break;
                    }

                    $manufacturerSupportVar = [
                        'variable' => 'manufacturer_support',
                        'variable_title' => $title,
                        'value_formatted' => $this->formatPriceBaseOnCurrencySetting($manufacturerSupport, 2)
                    ];

                    $tmpData['finance_variables'] = $this->_sortFinanceVariables($tmpData, $manufacturerSupportVar);
                }

                $this->_variables = $tmpData ?: [];
            }
        }

        return $this->_variables;
    }

    /**
     * If cash_price variable present, will add manufacturer support variable above Offer Price,
     * otherwise will append manufacturer support variable at the end of finance variable array.
     *
     * @return array
     */
    protected function _sortFinanceVariables($tmpData, $manufacturerSupportVar)
    {
        $financeVarKey = array_search('cash_price', array_column($tmpData['finance_variables'], 'variable'));

        if ($financeVarKey !== false) {
            array_splice($tmpData['finance_variables'], $financeVarKey, 0, [$manufacturerSupportVar]);
        }
        else {
            $tmpData['finance_variables'][] = $manufacturerSupportVar;
        }

        return $tmpData['finance_variables'];
    }

    /**
     * {@inheritDoc}
     */
    public function getFormattedTitle($title)
    {
        $matchReplace = [
            '{{ getTerm() }}' => $this->_getTermValue()
        ];

        return strtolower(str_replace(array_keys($matchReplace), $matchReplace, $title));
    }


    /**
     * Return Wish list item
     *
     * @return Mage_Wishlist_Model_Item
     */
    public function getWishListItem()
    {
        if ($this->_wishListItem === null) {
            $this->_wishListItem = $this->getData('wishlist');
        }

        return $this->_wishListItem;
    }

    /**
     * {@inheritDoc}
     */
    protected function _getTermValue()
    {
        if ($this->_term === null) {
            $this->_term = 0;
            $helperAll = $this->helper('rockar_all');
            $wishListItem = $this->getWishListItem();
            $financeData = $wishListItem ? $wishListItem->getData('finance_data') : null;
            $variables = $this->getFinanceVariables();

            if (!empty($financeData)) {
                $financeData = $helperAll->jsonDecode($financeData);
                $financeData = isset($financeData['group_id'], $financeData[$financeData['group_id']])
                    ? $financeData[$financeData['group_id']]
                    : $financeData;

                $this->_term = $financeData['term'] ?? $this->_term;

                foreach ($variables as $variable) {
                    if (isset($variable['variable']) && $variable['variable'] == 'one_monthly_payments_of') {
                        $this->_term = $this->_term - 1;
                    }
                }
            }
        }

        return $this->_term;
    }

    /**
     * {@inheritDoc}
     */
    public function getImage()
    {
        $wishListItem = $this->getWishListItem();

        return $wishListItem
            ? Mage::helper('rockar_checkout/confirmation')->getExteriorImage($wishListItem->getProduct())
            : null;
    }
}

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Orderamend
 * @author    Juris Krislauks <techteam@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Orderamend_Helper_Finance_Reorder extends Rockar_Orderamend_Helper_Finance_Reorder
{

    /**
     * @var Additional order item data from old order to add to new order amend
     */
    protected $_additionalQuoteData = [
        'deposit_source',
        'deposit_other_description'
    ];

    /**
     * Recalculate finances and save to quote
     * Added balloon_percentage to financeQuoteData params.
     *
     * @throws Mage_Core_Exception
     * @return string
     */
    public function saveFinanceOnReorder()
    {
        $orderAmendHelper = Mage::helper('rockar_orderamend');
        $quote = $orderAmendHelper->getQuote();
        $quoteItems = $quote->getItemsCollection();
        $helperAll = Mage::helper('rockar_all');
        $quoteItem = false;
        $accessories = [];

        foreach ($quoteItems as $item) {
            if ($item->getProductType() == 'simple') {
                $quoteItem = $item;
            }
        }

        $accessories = $quoteItem->getData('accessories')
            ? $helperAll->jsonDecode($quoteItem->getData('accessories'))
            : $accessories;
        $quoteFinanceData = $helperAll->jsonDecode($quoteItem->getData('finance_data'));

        $financeGroupId = $quoteFinanceData['group_id'];
        $financePaymentType = $quoteFinanceData['payment_type'];

        $product = Mage::getModel('catalog/product')->load($quoteItem->getProduct()->getId());
        // to get proper final price we need to set store and
        // customer group to the product because of catalog price rules
        $product->setCustomerGroupId($quote->getCustomerGroupId())->setStoreId($quote->getStoreId());
        $fullConfiguration = null;

        if ($quoteItem->getIsFullConfigurator()) {
            $fullConfiguration = (array) $helperAll->jsonDecode($quoteItem->getData('full_configuration'));
            $configuratorFinanceHelper = $this->getConfiguratorFinanceHelper();
            $configuratorFinanceHelper->insertSelectedCarConfigurationData($product, $fullConfiguration);
        }

        $params = [
            'product' => $product,
            'term' => $quoteFinanceData[$financeGroupId]['term'],
            'mileage' => $quoteFinanceData[$financeGroupId]['mileage'],
            'deposit' => $quoteFinanceData[$financeGroupId]['deposit'],
            'deposit_multiple' => $quoteFinanceData[$financeGroupId]['depositMultiple'],
            'maintenance' => $quoteFinanceData[$financeGroupId]['maintenance'],
            'accessories' => $accessories,
            'finance_payment_type' => $financePaymentType,
            'finance_group_id' => $quoteFinanceData['group_id'],
            'quote' => $quote,
            'is_full_configurator' => $quoteItem->getIsFullConfigurator(),
            'full_configuration' => $fullConfiguration
        ];

        if ($balloonPercentage = $quoteFinanceData[$financeGroupId]['balloonPercentage'] ?? false) {
            $params['balloon_percentage'] = $balloonPercentage;
        }

        // Set flag in registry to make sure configurator module extends finance data accordingly,
        // before getReorderFinanceQuoteData is called
        if ($quoteItem->getIsFullConfigurator()) {
            Mage::register('is_full_configurator', true, true);
        }

        $financeDataHelper = Mage::helper('rockar_orderamend/finance_data');
        $newFinanceQuoteData = $this->getReorderFinanceQuoteData($params);
        $price = $newFinanceQuoteData['rockar_price'];
        $oldOrder = Mage::getModel('sales/order')->load(Mage::getSingleton('adminhtml/session_quote')->getReordered());

        $orderItem = Mage::helper('rockar_checkout/order')->getFirstSimpleOrderItem($oldOrder);

        $orderFinanceDataVariables = $helperAll->jsonDecode($orderItem->getData('finance_data_variables'));
        $oldMonthlyPrice = (float) preg_replace(
            '/[^0-9.]/',
            '',
            $orderFinanceDataVariables['monthly_price']
        );
        $newMonthlyPrice = (float) $newFinanceQuoteData['monthly_price'];
        $newPaymentType = $newFinanceQuoteData['type'];

        $financeProduct = Mage::getModel('rockar_financingoptions/options')->load($newPaymentType, 'type');
        $isPayInFull = (int) $financeProduct->getData('pay_in_full');

        $financeOptions = $financeDataHelper->filterFinanceOptionsData(
            $this->_getOptions($params),
            $newPaymentType
        );

        if (!$newMonthlyPrice && !$isPayInFull || !$financeOptions) {
            Mage::throwException('Finances can not be recalculated for chosen product.');
        }

        $successMessage = $this->__("Finances successfully recalculated. | | | Used payment method {$newPaymentType}.");

        if (!$isPayInFull && $oldMonthlyPrice !== $newMonthlyPrice) {
            $successMessage .= $this->__("| | | Monthly price has changed from original order {$this->_formatPrice($oldMonthlyPrice)} to {$this->_formatPrice($newMonthlyPrice)}. ");
        }

        /**
         * Add notification if cashback value changed.
         */
        $oldCashback = $this->_formatPrice((float) preg_replace(
            '/[^0-9.]/',
            '',
            $orderFinanceDataVariables['cashback']
        ));
        $newCashback = $this->_formatPrice((float) $newFinanceQuoteData['cashback']);

        if ($oldCashback !== $newCashback) {
            $successMessage .= $this->__("| | | Cashback value has changed from original order {$oldCashback} to {$newCashback}.");
        }

        foreach ($quoteItems as $item) {
            // Save all data in quote item
            $quoteFinanceData['product_id'] = $product->getId();
            $quoteFinanceData['payment_type'] = $newPaymentType;
            $quoteFinanceData[$financeGroupId]['product_id'] = $product->getId();
            $quoteFinanceData[$financeGroupId]['payment_type'] = $newPaymentType;
            $item->setData('finance_data', $helperAll->jsonEncode($quoteFinanceData));
            $item->setData('finance_data_variables', $helperAll->jsonEncode($newFinanceQuoteData));
            $item->setData('finance_overlay', Mage::helper('rockar_all')->jsonEncode($financeOptions));
            $item->addData($this->_getAdditionalQuoteData($orderItem, $this->_additionalQuoteData));
            $item->setCustomPrice($price);
            $item->setOriginalCustomPrice($price);

            Mage::dispatchEvent(
                'checkout_quote_item_finance_update',
                [
                    'item' => $item,
                    'finance_data' => $newFinanceQuoteData,
                    'finance_group_id' => $financeGroupId
                ]
            );

            $item->save();
        }

        $quote->setData('finance_payment_type', $newPaymentType);
        $quote->setData('finance_payment_title', $financeProduct->getTitle());
        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals()->save();

        return $successMessage;
    }

    /**
     * Get Peppermint specific from old order data
     *
     * @param Mage_Sales_Model_Order_Item $orderItem
     * @param array $dataToGet
     * @return array $result
     */
    protected function _getAdditionalQuoteData($orderItem, $dataToGet)
    {
        $result = [];
        foreach ($dataToGet as $dataKey) {
            $result[$dataKey] = $orderItem->getData($dataKey);
        }

        return $result;
    }

    /**
     * Added balloon_percentage passing to QuoteData::prepareParams.
     *
     * @param $params
     * @throws Mage_Core_Exception
     * @return array
     */
    public function getReorderFinanceQuoteData($params)
    {
        $functionParams = Peppermint_FinancingOptions_Helper_Interfaces_QuoteData::prepareParams(
            $params['product'],
            $params['mileage'],
            $params['term'],
            $params['deposit'],
            $params['deposit_multiple'],
            $params['maintenance'],
            Rockar_FinancingOptions_Model_Calculation_Type_Abstract::CALC_TYPE_REORDER,
            $params['accessories'],
            $params['finance_payment_type'],
            $params['finance_group_id'],
            $params['quote']->getCouponCode() ?: false,
            $params['balloon_percentage'] ?? 0
        );

        $quote = $params['quote'];

        $reorderData = [
            'customer_group_id' => $quote->getCustomerGroupId(),
            'store_id' => $quote->getStoreId(),
            'accessories' => $params['accessories'],
            'is_full_configurator' => $params['is_full_configurator'],
            'full_configuration' => $params['full_configuration']
        ];

        Mage::register('reorder_data', $reorderData);

        $financeDataHelper = Mage::helper('rockar_orderamend/finance_data');

        return $financeDataHelper->getFinanceQuoteData($functionParams);
    }

    /**
     * Return Options and Finance Data
     * Added balloon_percentage passing to OptionsByParams::prepareParams.
     *
     * @param mixed $params
     * @return array
     */
    protected function _getOptions($params)
    {
        $financeDataHelper = Mage::helper('rockar_orderamend/finance_data');

        $optionsByParams = Peppermint_FinancingOptions_Helper_Interfaces_OptionsByParams::prepareParams(
            $params['product'],
            $params['mileage'],
            $params['term'],
            $params['deposit'],
            $params['deposit_multiple'],
            $params['maintenance'],
            Rockar_FinancingOptions_Model_Calculation_Type_Abstract::CALC_TYPE_REORDER,
            $params['quote']->getCouponCode() ?: false,
            $params['balloon_percentage'] ?? 0
        );

        return ['options' => $financeDataHelper->getFinancingOptionsByParams($optionsByParams)];
    }
}

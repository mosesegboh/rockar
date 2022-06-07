<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

require_once Mage::getModuleDir('controllers', 'Rockar2_FinancingOptions') . DS . 'AjaxController.php';

class Peppermint_FinancingOptions_AjaxController extends Rockar2_FinancingOptions_AjaxController
{
    /**
     * generates _getOptions.
     *
     * @return array
     */
    protected function _getOptions()
    {
        $return = [];
        $request = $this->getRequest();
        $productId = (int) $request->getParam('productId', 0);
        $calcType = $request->getParam('calc_type', Rockar_FinancingOptions_Model_Calculation_Type_Abstract::CALC_TYPE_CAR_FINDER);
        $paramsData = $request->getParam('slider_data', []);

        $financeDataParams = $this->_helperConfig->getAllSliderState(false);
        $params = $paramsData;

        if (isset($paramsData['method'], $financeDataParams[$paramsData['method']])) {
            $params = array_merge($financeDataParams[$paramsData['method']], $paramsData);
        }

        if (isset($productId)) {
            $optionsByParams = Peppermint_FinancingOptions_Helper_Interfaces_OptionsByParams::prepareParams(
                $productId,
                $params['mileage'] ?? 0,
                $params['term'] ?? 0,
                $params['deposit'] ?? 0,
                $params['depositMultiple'] ?? 0,
                $params['maintenance'] ?? 0,
                $calcType,
                Mage::getSingleton('checkout/cart')->getQuote()->getCouponCode() ?: false,
                $params['balloonPercentage'] ?? 0
            );

            $cache = Mage::helper('peppermint_financingoptions/cache');
            $cache->enableCache();

            $return = ['options' => $this->_helper->getFinancingOptionsByParams($optionsByParams)];

            $cache->disableCache();
        }

        return $return;
    }

    /**
     * Save The Data From Finance overlay And return variables for Finance Quote.
     *
     * @return void
     */
    public function saveOnPdpAction()
    {
        $params = $this->getRequest()->getParams();
        $savedData = $this->_helper->getSavedFinanceData();
        $financeGroupId = $params['group_id'] ?? $savedData['group_id'];
        $paramsData = [];

        if (isset($savedData[$financeGroupId])) {
            $paramsData = array_merge($savedData[$financeGroupId], $params);
            $paramsData['payment_type'] = $savedData['payment_type'] ?? '';
            $paramsData['group_id'] = $financeGroupId;
            $savedData[$financeGroupId] = $paramsData;
        }
        $paramsData['calc_type'] = Rockar_FinancingOptions_Model_Calculation_Type_Abstract::CALC_TYPE_PDP;
        $this->_helper->setSavedFinanceData($paramsData);

        $accessoriesHelper = Mage::helper('rockar_accessories');
        $accessories = $accessoriesHelper->getSelectedAccessoriesPerProduct($paramsData['product_id']);

        $functionParams = Peppermint_FinancingOptions_Helper_Interfaces_QuoteData::prepareParams(
            (int) $paramsData['product_id'],
            $paramsData['mileage'] ?? 0,
            $paramsData['term'] ?? 0,
            $paramsData['deposit'] ?? 0,
            $paramsData['depositMultiple'] ?? 0,
            $paramsData['maintenance'] ?? 0,
            $paramsData['calc_type'],
            $accessories,
            $paramsData['payment_type'],
            $financeGroupId,
            false,
            $paramsData['balloonPercentage'] ?? 0
        );

        $financeQuoteData = $this->_helper->getFinanceQuoteData($functionParams);

        $this->_getResponse($financeQuoteData);
    }

    /**
     * Save The Data From Finance overlay to quote item And return variables for Finance Quote.
     *
     * @return void
     */
    public function saveOnCheckoutAction()
    {
        /**
         * @var Mage_Sales_Model_Quote_Item
         */
        $quoteItem = Mage::helper('rockar_checkout')->getQuoteItem();
        $quote = $quoteItem->getQuote();
        $quoteFinanceData = $quoteItem->getData('finance_data');
        $quoteFinanceData = $quoteFinanceData ? $this->_helperAll->jsonDecode($quoteFinanceData) : [];

        $params = $this->getRequest()->getParams();
        $financeGroupId = $params['group_id'] ?? 0;
        $financePaymentType = $params['payment_type'] ?? '';

        if (!isset($quoteFinanceData[$financeGroupId])) {
            $quoteFinanceData[$financeGroupId] = [];
        }
        $quoteFinanceData[$financeGroupId] = $paramsData = array_merge($quoteFinanceData[$financeGroupId], $params);
        $quoteFinanceData['group_id'] = $financeGroupId;
        $quoteFinanceData['method'] = $financeGroupId;
        $quoteFinanceData['payment_type'] = $financePaymentType;

        $saveInQuote = true;

        if (array_filter($paramsData)) {
            // Save all params to the session
            $this->_helper->setSavedFinanceData($paramsData);
        } else {
            $saveInQuote = false;
        }

        if (!isset($paramsData['accessories'])) {
            $accessories = $quoteItem->getData('accessories');
            $accessories = $accessories ? $this->_helperAll->jsonDecode($accessories) : [];
        } else {
            $accessories = $paramsData['accessories'];
        }

        $productId = isset($paramsData['product_id']) ? (int) $paramsData['product_id'] : 0;
        $term = isset($paramsData['term']) ? (int) $paramsData['term'] : 0;
        $mileage = isset($paramsData['mileage']) ? (int) $paramsData['mileage'] : 0;
        $deposit = isset($paramsData['deposit']) ? (float) $paramsData['deposit'] : 0;
        $depositMultiple = isset($paramsData['depositMultiple']) ? (int) $paramsData['depositMultiple'] : 0;
        $maintenance = isset($paramsData['maintenance']) ? (int) $paramsData['maintenance'] : 0;
        $method = $financeGroupId;
        $balloonPercentage = $paramsData['balloonPercentage'] ?? 0;

        $functionParams = Peppermint_FinancingOptions_Helper_Interfaces_QuoteData::prepareParams(
            $productId,
            $mileage,
            $term,
            $deposit,
            $depositMultiple,
            $maintenance,
            Rockar_FinancingOptions_Model_Calculation_Type_Abstract::CALC_TYPE_QUOTE,
            $accessories,
            $financePaymentType,
            $financeGroupId,
            Mage::getSingleton('checkout/cart')->getQuote()->getCouponCode() ?: false,
            $balloonPercentage
        );

        $financeQuoteData = $this->_helper->getFinanceQuoteData($functionParams);
        $paramsData['payment_type'] = $financePaymentType;

        if ($saveInQuote) {
            $price = $financeQuoteData['rockar_price'];
            // Save all data in quote item
            $quoteItem->setData('finance_data', $this->_helperAll->jsonEncode($quoteFinanceData));
            $quoteItem->setData('finance_data_variables', $this->_helperAll->jsonEncode($financeQuoteData));
            $quoteItem->setCustomPrice($price);
            $quoteItem->setOriginalCustomPrice($price);
            $this->getRequest()
                ->setParam(
                    'slider_data',
                    [
                        'term' => $term,
                        'mileage' => $mileage,
                        'deposit' => $deposit,
                        'depositMultiple' => $depositMultiple,
                        'maintenance' => $maintenance,
                        'method' => $method
                    ]
                );
            $this->getRequest()
                ->setParam('productId', $productId);

            $financeOptions = $this->_filterFinanceOptionsData($this->_getOptions(), $financePaymentType);
            $quoteItem->setData(
                'finance_overlay',
                Mage::helper('rockar_all')
                    ->jsonEncode($financeOptions)
            );

            Mage::dispatchEvent(
                'checkout_quote_item_finance_update',
                [
                    'item' => $quoteItem,
                    'finance_data' => $financeQuoteData,
                    'finance_group_id' => $financeGroupId
                ]
            );

            $quoteItem->save();

            $quote->setData('finance_payment_type', $financePaymentType);
            $quote->collectTotals()
                ->save();
        }

        $this->_getResponse($financeQuoteData);
    }

    /**
     * {@inheritDoc}
     */
    public function progressOnCheckoutAction()
    {
        Mage::helper('peppermint_financingoptions/cache')->enableCache();
        parent::progressOnCheckoutAction();
    }

    /**
     * {@inheritDoc}
     */
    public function optionsAction()
    {
        Mage::helper('peppermint_financingoptions/cache')->enableCache();
        $this->sendJson($this->_helper->addOptionVariableClassNames($this->_getOptions()));
    }
}

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Orderamend
 * @author    Juris Krislauks <techteam@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Orderamend_Helper_Finance_Data extends Rockar_Orderamend_Helper_Finance_Data
{
    /**
     * Return PDP variables for finance quote
     * Added balloon_percentage passing to calculationModel.
     *
     * @param Rockar_FinancingOptions_Model_Interfaces_QuoteData $params
     *
     * @return Rockar_FinancingOptions_Model_Options
     */
    protected function _getFinanceQuoteVariables(Rockar_FinancingOptions_Model_Interfaces_QuoteData $params)
    {
        $product = $params->getProductId();
        $annualMileage = $params->getMileage();
        $term = $params->getTerm();
        $deposit = $params->getDeposit();
        $depositMultiple = $params->getDepositMultiple();
        $maintenance = $params->getMaintenance();
        $calcType = $params->getCalcType();
        $groupId = $params->getGroupId();
        $balloonPercentage = $params->getBalloonPercentage();

        if (is_numeric($product)) {
            $product = Mage::getModel('catalog/product')->load((int) $product);
        }

        $helper = Mage::helper('financing_options');

        Mage::dispatchEvent(
            'rockar_financing_options_get_quote_variables',
            [
                'product' => $product,
                'params' => $params
            ]
        );

        $reorderData = Mage::registry('reorder_data');
        $customerGroupId = $reorderData['customer_group_id'];
        $storeId = $reorderData['store_id'];

        /**
         * @var Rockar_FinancingOptions_Model_Calculation
         */
        $optionsCollection = $this->_getOptionsCollection($storeId, $customerGroupId, $groupId, $params)->load();

        /**
         * Get finance data by productId and other filter variables.
         *
         * @var Rockar_FinancingOptions_Model_Resource_Data_Collection
         */
        $financeDataCollection = $helper->getFinancingDataPerProduct($product->getId())
            ->addFieldToFilter('annual_mileage', ['in' => [$annualMileage, 0]])
            ->addFieldToFilter('term', ['eq' => $term])
            ->addFieldToFilter('payment_plan', [['in' => [0, $depositMultiple]], ['null' => true]])
            ->addFieldToFilter('maintenance', $maintenance);

        $variableHelper = Mage::helper('financing_options/variables');
        $variableHelper->setProduct($product);
        $financeDataCollection->addTermsData();

        foreach ($optionsCollection as $option) {
            /**
             * @var Rockar_FinancingOptions_Model_Data
             */
            $financeData = $financeDataCollection->getItemByColumnValue('payment_type', $option->getType());

            if ((!$financeData && !$option->isPayInFullPayment()) || ($financeData && !$financeData->getTermId())) {
                continue;
            }
            $option->setData('finance_data', $financeData);
            $option->setData('variables', $option->addOptionPdpVariables($option->getId()));

            /**
             * Init calculation Model.
             */
            $calculationModel = $helper->getCalculationModel($calcType);
            $calculationModel->setOption($option);
            $calculationModel->setProduct($product);
            $calculationModel->setParams($term, $annualMileage, $deposit, $depositMultiple, $balloonPercentage);

            // Init Variables helper
            $variableHelper->setOption($option)->init($calculationModel);

            // Add calculated values
            try {
                $helper->setCalculationModel($calculationModel);

                $calc = $helper->getCalculationsPerOption($product, $option);

                $option->setData('calc', $calc);
                $option->setData('calc_model', $calculationModel);

                if ($calc['min_deposit_validation']['value']) {
                    $newVariables = [];

                    foreach ($option->getData('variables') as $variableId => $fVariable) {
                        $type = (int) $fVariable['type'];
                        //Format customer deposit variable title if customer have positive equity(pxValue > outstanding finance)
                        $helper->formatCustomerDepositVarText($fVariable, $option);

                        if ($type
                            != Rockar_FinancingOptions_Model_Adminhtml_System_Config_Source_VariableType::TYPE_STRING
                        ) {
                            $fVariable['value']
                                = $variableHelper->renderVariableCalculationField($fVariable['calculation']);
                        } else {
                            $fVariable['value']
                                = $variableHelper->renderVariableCalculationString($fVariable['calculation']);
                        }
                        $fVariable['value_formatted'] = $helper->formatVariableValue($fVariable);
                        $newVariables[$variableId] = $fVariable;
                    }

                    $newVariables = $helper->validateDependencies($newVariables);
                    $newVariables = $this->parseOptionVariablesTitles($newVariables, $params);

                    $option->setData('variables', $newVariables);

                    break; // Stop foreach on first correct finance options
                }
            } catch (Exception $e) {
                Mage::logException($e);
                continue;
            }
        }

        /**
         * @var Rockar_FinancingOptions_Model_Options
         */
        $_option = null;

        foreach ($optionsCollection as $option) {
            $calc = $option->getData('calc');

            if ($option->hasData('finance_data')
                && (isset($calc['min_deposit_validation']) && $calc['min_deposit_validation']['value'])
            ) {
                $_option = $option;
                break;
            }
        }

        return $_option ?? $optionsCollection->getFirstItem();
    }

    /**
     * Returns Data For Finance Quote
     * Added calculated balloon_percentage and balloon_amount to financeQuoteData response.
     *
     * @param Rockar_FinancingOptions_Model_Interfaces_QuoteData $params
     *
     * @throws Mage_Core_Exception
     * @return array [car_data, finance_variables, monthly_price, total_deposit, customer_deposit]
     */
    public function getFinanceQuoteData(Rockar_FinancingOptions_Model_Interfaces_QuoteData $params)
    {
        if (isset($params['payment_type'])) {
            Mage::unregister('payment_type');
            Mage::register('payment_type', $params['payment_type']);
        }

        /**
         * @var Mage_Catalog_Model_Product
         */
        $helper = Mage::helper('financing_options');
        $product = $params->getProductId();
        $accessories = $params->getAccessories();

        $option = $this->_getFinanceQuoteVariables($params);

        $variables = $option['variables'] ?? [];
        $calculations = $option['calc'] ?? [];

        if (is_numeric($product)) {
            $product = Mage::getModel('catalog/product')->load((int) $product);
        }

        $financeQuoteData = new Varien_Object([
            'car_data' => $helper->getCarData($product, $option, $accessories, $params, $calculations),
            'finance_variables' => $variables,
            'monthly_price' => $this->_getValueFromCalc($calculations, 'monthly_price'),
            'total_deposit' => $this->_getValueFromCalc($calculations, 'total_deposit'),
            'customer_deposit' => $this->_getValueFromCalc($calculations, 'customer_deposit'),
            'pay_deposit' => $this->_getValueFromCalc($calculations, 'pay_deposit'),
            'total_amount_payable' => $this->_getValueFromCalc($calculations, 'total_amount_payable'),
            'balance_to_finance' => $this->_getValueFromCalc($calculations, 'balance_to_finance'),
            'cash_deposit' => $this->_getValueFromCalc($calculations, 'cash_deposit'),
            'balloon_percentage' => $this->_getValueFromCalc($calculations, 'balloon_percentage'),
            'balloon_amount' => $this->_getValueFromCalc($calculations, 'balloon_amount'),
            'cashback' => $this->_getValueFromCalc($calculations, 'cashback'),
            'option_rental_price_pr1' => $this->_getValueFromCalc($calculations, 'option_rental_price_pr1'),
            'type' => $option->getType(),
            'is_hire' => (int) $option->isHirePayment(),
            'is_leasing' => (int) $option->isLeasingPayment(),
            'is_pay_in_full' => (int) $option->isPayInFullPayment(),
            'is_business' => $option->isBusiness(),
            'rockar_price' => $this->_getValueFromCalc($calculations, 'product_price'),
            'save_off_rrp' => $product->getSaveOffPrice(),
            'lead_time' => 0
        ]);

        Mage::dispatchEvent(
            'rockar_financingoptions_finance_quote_data',
            [
                'finance_quote_data' => $financeQuoteData,
                'product' => $product,
                'accessories' => $accessories
            ]
        );

        return $financeQuoteData->toArray();
    }

    /**
     * Return options per customer group, store view and rendered variables
     * Passing balloon_percentage to calculationModel.
     *
     * @param Rockar_FinancingOptions_Model_Resource_Data_Collection $financeDataCollection
     * @param Rockar_FinancingOptions_Model_Interfaces_OptionsByParams $optionsByParams
     *
     * @return Rockar_FinancingOptions_Model_Resource_Options_Collection
     */
    public function getOptions(
        $financeDataCollection,
        Rockar_FinancingOptions_Model_Interfaces_OptionsByParams $optionsByParams
    ) {
        $this->_optionsByParams = $optionsByParams;

        $product = $optionsByParams->getProductId();
        $term = $optionsByParams->getTerm();
        $annualMileage = $optionsByParams->getMileage();
        $deposit = $optionsByParams->getDeposit();
        $depositMultiple = $optionsByParams->getDepositMultiple();
        $calcType = $optionsByParams->getCalcType();
        $optionsCollection = $this->_getOptions();
        $balloonPercentage = $optionsByParams->getBalloonPercentage();
        $helper = Mage::helper('financing_options');
        // @var Mage_Catalog_Model_Product
        if (is_numeric($product)) {
            $product = Mage::getModel('catalog/product')->load((int) $product);
        }

        if (!$product && !$product->getId()) {
            return $optionsCollection;
        }

        Mage::dispatchEvent(
            'rockar_financing_options_get_options',
            [
                'product' => $product,
                'params' => $optionsByParams
            ]
        );

        $variableHelper = Mage::helper('financing_options/variables');
        $variableHelper->setProduct($product);
        $financeDataCollection->addTermsData();

        foreach ($optionsCollection as $option) {
            /**
             * @var Rockar_FinancingOptions_Model_Data
             */
            $financeData = $financeDataCollection->getItemByColumnValue('payment_type', $option->getType());

            if ((!$financeData && !$option->isPayInFullPayment()) || ($financeData && !$financeData->getTermId())) {
                continue;
            }
            $option->setData('finance_data', $financeData);

            /**
             * Init calculation Model.
             */
            $calculationModel = $helper->getCalculationModel($calcType);
            $calculationModel->setOption($option);
            $calculationModel->setProduct($product);
            $calculationModel->setParams($term, $annualMileage, $deposit, $depositMultiple, $balloonPercentage);
            // Init Variables helper
            $variableHelper->setOption($option)->init($calculationModel);

            // Add calculated values
            try {
                $calc = $helper->getCalculationsPerOption($product, $option);
                $option->setData('calc', $calc);

                if ($calc['min_deposit_validation']['value']) {
                    $newVariables = [];

                    foreach ($option->getData('variables') as $variableId => $fVariable) {
                        $type = (int) $fVariable['type'];
                        //Format customer deposit variable title if customer have positive equity(pxValue > outstanding finance)
                        $helper->formatCustomerDepositVarText($fVariable, $option);

                        $fVariable['value'] = ($type != Rockar_FinancingOptions_Model_Adminhtml_System_Config_Source_VariableType::TYPE_STRING)
                            ? $variableHelper->renderVariableCalculationField($fVariable['calculation'])
                            : $variableHelper->renderVariableCalculationString($fVariable['calculation']);

                        $fVariable['value_formatted'] = $helper->formatVariableValue($fVariable);
                        $newVariables[$variableId] = $fVariable;
                    }

                    $newVariables = $helper->validateDependencies($newVariables);
                    $newVariables = $this->parseOptionVariablesTitles($newVariables, $optionsByParams);
                    $option->setData('variables', $newVariables);
                }
            } catch (Exception $e) {
                continue;
            }
        }

        return $optionsCollection;
    }
}

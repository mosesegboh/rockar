<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Lucaci Stefan <lucacistefan.alexandru@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_FinancingOptions_Helper_Data extends Rockar2_FinancingOptions_Helper_Data
{
    /**
     * Finance parameters
     *
     * @var array
     */
    protected $_financeParams = [
        'method',
        'monthlypay',
        'deposit',
        'term',
        'mileage',
        'payinfull',
        'depositMultiple',
        'maintenance',
        'acceptedFinance',
        'balloonPercentage'
    ];

    /**
     * Variable code and title mapper
     *
     * @var array
     */
    protected $_variablesTitleMap = ['customer_deposit' => 'Deposit Balance (Inc. Trade-in Surplus)*'];

    /**
     * Return options per customer group, store view and rendered variables.
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
        $balloonPercentage = $optionsByParams->getBalloonPercentage();
        $calcType = $optionsByParams->getCalcType();
        $optionsCollection = $this->_getOptions();
        // @var Mage_Catalog_Model_Product
        if (is_numeric($product)) {
            $product = Mage::helper('peppermint_financingoptions/cache')->getProduct((int) $product);
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

            // Init calculation Model
            $this->_calculationModel = $this->getCalculationModel($calcType);
            $this->_calculationModel->setOption($option);
            $this->_calculationModel->setProduct($product);
            $this->_calculationModel->setParams($term, $annualMileage, $deposit, $depositMultiple, $balloonPercentage);
            // Init Variables helper
            $variableHelper->setOption($option)->init($this->_calculationModel);

            // Add calculated values
            try {
                $calc = $this->getCalculationsPerOption($product, $option);
                $option->setData('calc', $calc);

                if ($calc['min_deposit_validation']['value']) {
                    $newVariables = [];

                    foreach ($option->getData('variables') as $variableId => $variableData) {
                        $type = (int) $variableData['type'];
                        //Format customer deposit variable title if customer have positive equity(pxValue > outstanding finance)
                        $this->formatCustomerDepositVarText($variableData, $option);

                        if ($type
                            != Rockar_FinancingOptions_Model_Adminhtml_System_Config_Source_VariableType::TYPE_STRING
                        ) {
                            $variableData['value']
                                = $variableHelper->renderVariableCalculationField($variableData['calculation']);
                        } else {
                            $variableData['value']
                                = $variableHelper->renderVariableCalculationString($variableData['calculation']);
                        }
                        $variableData['value_formatted'] = $this->formatVariableValue($variableData);
                        $newVariables[$variableId] = $variableData;
                    }

                    $newVariables = $this->validateDependencies($newVariables);
                    $newVariables = $this->parseOptionVariablesTitles($newVariables, $optionsByParams);
                    $option->setData('variables', $newVariables);
                }
            } catch (Exception $e) {
                continue;
            }
        }

        return $optionsCollection;
    }

    /**
     * Returns Data For Finance Quote.
     *
     * @param Rockar_FinancingOptions_Model_Interfaces_QuoteData $params
     *
     * @return array [car_data, finance_variables, monthly_price, total_deposit, customer_deposit]
     */
    public function getFinanceQuoteData(Rockar_FinancingOptions_Model_Interfaces_QuoteData $params)
    {
        $product = $params->getProductId();
        $accessories = $params->getAccessories();

        $option = $this->_getFinanceQuoteVariables($params);
        $variables = $option['variables'] ?? [];
        $calculations = $option['calc'] ?? [];

        if (is_numeric($product)) {
            $product = Mage::getModel('catalog/product')->load((int) $product);
        }

        $financeQuoteData = new Varien_Object([
            'car_data' => $this->getCarData($product, $option, $accessories, $params, $calculations),
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
            'is_instalment' => (int) $option->isInstalment(),
            'is_business' => $option->isBusiness(),
            'rockar_price' => $this->_getValueFromCalc($calculations, 'product_price'),
            'save_off_rrp' => $product->getSaveOffPrice(),
            'lead_time' => 0,
            'model_code' => null
        ]);

        Mage::dispatchEvent(
            'rockar_financingoptions_finance_quote_data',
            [
                'finance_quote_data' => $financeQuoteData,
                'calculation_model' => $this->_calculationModel,
                'product' => $product,
                'accessories' => $accessories
            ]
        );

        return $financeQuoteData->toArray();
    }

    /**
     * Return PDP variables for finance quote.
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
        $balloonPercentage = $params->getBalloonPercentage();
        $maintenance = $params->getMaintenance();
        $calcType = $params->getCalcType();
        $groupId = $params->getGroupId();

        if (is_numeric($product)) {
            $product = Mage::getModel('catalog/product')->load((int) $product);
        }
        /**
         * @var Mage_Catalog_Model_Product
         */
        $helper = Mage::helper('financing_options');

        Mage::dispatchEvent(
            'rockar_financing_options_get_quote_variables',
            [
                'product' => $product,
                'params' => $params
            ]
        );

        /**
         * @var Rockar_FinancingOptions_Model_Calculation
         */
        $optionsCollection = Mage::getModel('rockar_financingoptions/options')
            ->getOptionsPerStoreAndCustomerGroupCollection()
            ->addFieldToFilter('group_id', $groupId)
            ->load();

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
                $option->setData('calc_model', $this->_calculationModel);

                if ($calc['min_deposit_validation']['value']) {
                    $newVariables = [];

                    foreach ($option->getData('variables') as $variableId => $variableData) {
                        $type = (int) $variableData['type'];
                        //Format customer deposit variable title if customer have positive equity(pxValue > outstanding finance)
                        $this->formatCustomerDepositVarText($variableData, $option);

                        if ($type
                            != Rockar_FinancingOptions_Model_Adminhtml_System_Config_Source_VariableType::TYPE_STRING
                        ) {
                            $variableData['value']
                                = $variableHelper->renderVariableCalculationField($variableData['calculation']);
                        } else {
                            $variableData['value']
                                = $variableHelper->renderVariableCalculationString($variableData['calculation']);
                        }
                        $variableData['value_formatted'] = $helper->formatVariableValue($variableData);
                        $newVariables[$variableId] = $variableData;
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

            if ($option->hasData('finance_data') && (isset($calc['min_deposit_validation']) && $calc['min_deposit_validation']['value'])) {
                $_option = $option;
                break;
            }
        }

        return ($_option == null) ? $optionsCollection->getFirstItem() : $_option;
    }

    /**
     * Return PayInFull Option Payment with Group Id.
     *
     * @return array
     */
    public function getDefaultPayInFullPayment()
    {
        /**
         * @var Rockar_FinancingOptions_Model_Options
         */
        $options = $this->_getOptions()->getItems();

        foreach ($options as $option) {
            if ($option->getPayInFull()) {
                $result[] = [
                    'payment_type' => $option->getType(),
                    'group_id' => (int) $option->getGroupId()
                ];
            }
        }

        return $result ?: [[
            'payment_type' => null,
            'group_id' => null
        ]];
    }

    /**
     * Return calculated variables per option
     * Added balloon_percentage and balloon_amount to calculated variables
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Rockar_FinancingOptions_Model_Options $option
     *
     * @return array
     * @throws Mage_Core_Exception
     */
    public function getCalculationsPerOption($product, $option)
    {
        if ($this->_calculationModel === null) {
            $calculationModel = Mage::getModel('rockar_financingoptions/calculation_carfinder');
            $calculationModel->setOption($option);
            $calculationModel->setProduct($product);
        } else {
            $calculationModel = $this->_calculationModel;
        }
        $calculated = [];

        if (!$calculationModel->getMaxDepositValidation()) {
            Mage::throwException('Invalid Maximum Deposit');
        }

        $calculatedVariables = [
            'monthly_price' => 'monthly_cost',
            'product_price' => 'product_price',
            'cashback' => 'cashback',
            'min_deposit_validation' => 'min_deposit_validation',
            'total_deposit' => 'total_deposit',
            'cash_deposit' => 'cash_deposit',
            'total_amount_payable' => 'total_amount_payable',
            'customer_deposit' => 'customer_deposit_display',
            'pay_deposit' => 'pay_deposit',
            'balance_to_finance' => 'balance_to_finance',
            'color_rental_price' => 'color_rental_price',
            'balloon_percentage' => 'balloon_percentage',
            'balloon_amount' => 'balloon_amount'
        ];

        foreach ($calculatedVariables as $variable => $method) {
            $value = $calculationModel->getDataUsingMethod($method);
            $calculated[$variable] = [
                'value' => $value
            ];
        }

        /**
         * Get variables from finance data
         *
         * @var Rockar_FinancingOptions_Model_Data $financeData
         */
        $financeData = $option->getFinanceData();
        if ($financeData !== null) {
            $calculated['car_rental']['value'] = (float)$financeData->getData('lease_rental');
            $calculated['option_rental_price_pr1']['value'] = (float)$financeData->getData('option_rental_price_pr1');
        }

        return $calculated;
    }

    /**
     * Get group id for current store based on method type.
     *
     * @param integer $methodType
     *
     * @return string|null
     */
    public function getGroupIdByMethodType($methodType)
    {
        return Mage::getModel('rockar_financingoptions/group')->getCollection()
            ->addFieldToFilter('website', Mage::app()->getStore()->getId())
            ->addFieldToFilter('method_type', $methodType)
            ->addFieldToSelect('group_id')
            ->setCurPage(1)
            ->setPageSize(1)
            ->getFirstItem()
            ->getGroupId();
    }

    /**
     * Format customer_deposit variable title if customer have trade in surplus/positive equity
     * This is to avoid creating a new variable since many functions relies on this particular variables
     *
     * @param array $varData
     * @param Peppermint_FinancingOptions_Model_Options $option
     *
     * @return void
     */
    public function formatCustomerDepositVarText(&$varData, $option)
    {
        $newTitle = $this->_variablesTitleMap[$varData['variable']] ?? false;

        //This should only run if it's not pay in full
        if (
            !$option->isPayInFullPayment()
            && $newTitle && $this->_calculationModel->getIsTradeInSurplus()
        ) {
            $varData['variable_title'] = $newTitle;
        }

        return;
    }

    /**
     * Get part exchange values
     *
     * @return array
     */
    public function getAdditionalPartExchangeDataNonJson()
    {
        $result = [];

        $partExchange = Mage::helper('rockar_partexchange')->loadPartExchangeFromSession(
            Rockar_PartExchange_Helper_Data::CUSTOMER_PART_EXCHANGE_SESSION_KEY
        );

        if ($partExchange) {
            $result = [
                'carDetailsUrl' => Mage::getUrl('partexchange/details'),
                'resetUrl' => Mage::getUrl('partexchange/crud/reset', ['_secure' => true, 'form_key' => Mage::getSingleton('core/session')->getFormKey()]),
                'explanatoryText' => $this->stripTags(Mage::getStoreConfig(
                    Rockar_PartExchange_Block_Filters::XML_PART_EXCHANGE_EXPLANATORY_TEXT), '<p><br>'),
                'carAlternativeDetailsUrl' => Mage::getUrl('partexchange/alternatives'),
                'saveToSessionUrl' => Mage::getUrl('partexchange/crud/saveToSession'),
                'savedPxList' => $this->getAllPartExchanges(),
                'makeUrl' => Mage::getUrl('partexchange/details/getMake'),
                'rangeUrl' => Mage::getUrl('partexchange/details/getRange'),
                'modelUrl' => Mage::getUrl('partexchange/details/getModel'),
                'yearUrl' => Mage::getUrl('partexchange/details/getYear'),
                'colourUrl' => Mage::getUrl('partexchange/details/getColour'),
                'derivativeUrl' => Mage::getUrl('partexchange/details/getDerivative'),
                'customCarUrl' => Mage::getUrl('partexchange/details/customCar'),
                'activePxUrl' => Mage::getUrl('partexchange/switch/'),
            ];
        }

        return $result;
    }

    /**
     * Rewrite of parent function to add form key to links
     *
     * {@inheritDoc}
     */
    public function getAdditionalPartExchangeData()
    {
        return Mage::helper('rockar_all')->jsonEncode($this->getAdditionalPartExchangeDataNonJson());
    }

    /**
     * Get part exchange data
     *
     * @return array
     */
    public function getPartExchangeData()
    {
        $result = [];

        $partExchange = Mage::helper('rockar_partexchange')->loadPartExchangeFromSession(
            Rockar_PartExchange_Helper_Data::CUSTOMER_PART_EXCHANGE_SESSION_KEY
        );

        if ($partExchange) {
            $result = [
                'pxId' => $partExchange->getId(),
                'vrm' => $partExchange->getVrm(),
                'title' => $partExchange->getCap()['model'],
                'mileage' => $partExchange->getMileage(),
                'outstandingFinance' => (int) $partExchange->getOutstandingFinance(),
                'activeCondition' => (int) $partExchange->getCarCondition(),
                'pxValue' => (int) $partExchange->getPartExchangeValue(),
                'valuationUrl' => Mage::getUrl('partexchange/valuation'),
                'saveValuationUrl' => Mage::getUrl('partexchange/crud/save', ['_secure' => true, 'form_key' => Mage::getSingleton('core/session')->getFormKey()]),
            ];

            if ($partExchange->getPxId()) {
                $result['pxId'] = (int) $partExchange->getPxId();
            }
        }

        return $result;
    }

    /**
     * Rewrite parent function to add form key
     *
     * {@inheritDoc}
     */
    public function getPartExchangeJson()
    {
        return Mage::helper('rockar_all')->jsonEncode($this->getPartExchangeData());
    }

    /**
     * Rewrite parent function to load product from cache if enabled
     *
     * {@inheritDoc}
     */
    public function getFinancingOptionsByParams($optionsByParams)
    {
        $annualMileage = $optionsByParams->getMileage();
        $product = $optionsByParams->getProductId();
        $term = $optionsByParams->getTerm();
        $depositMultiple = $optionsByParams->getDepositMultiple();

        if (is_numeric($product)) {
            $product = Mage::helper('peppermint_financingoptions/cache')->getProduct((int) $product);
            $optionsByParams->setProductId($product);
        }

        /**
         * Get finance data by productId and other filter variables
         *
         * @var $financeDataCollection Rockar_FinancingOptions_Model_Resource_Data_Collection
         */
        $financeDataCollection = $this->getFinancingDataPerProduct($product->getId())
            ->addFieldToFilter('annual_mileage', ['in' => [$annualMileage, 0]])
            ->addFieldToFilter('term', ['eq' => $term])
            ->addFieldToFilter('payment_plan', [['in' => [0, $depositMultiple]], ['null' => true]])
            ->addFieldToFilter('maintenance', $optionsByParams->getMaintenance());

        /**
         * Get financing option collection with rendered variables
         */
        $optionsCollection = $this->getOptions($financeDataCollection, $optionsByParams);

        /**
         * Merge/Split Options by Groups
         */
        return $this->optionsCollectionFilterPerGroup($optionsCollection);
    }

    /**
     * Rewrite parent function to adjust variable formatting
     * Format option variable value
     *
     * @param [] $variable
     *
     * @return string
     */
    public function formatVariableValue($variable)
    {
        $value = $variable['value'];
        $type = (int)$variable['type'];
        $suffix = $variable['value_suffix'];

        switch ($type) {
            case Rockar_FinancingOptions_Model_Adminhtml_System_Config_Source_VariableType::TYPE_PRICE:
                $result = str_replace(
                    ',',
                    ' ',
                    preg_replace(
                        '/([a-zA-Z])/',
                        '${0} ',
                        preg_replace(
                            '/\.[0-9]+/',
                            '',
                            Mage::helper('core')->currency(round($value), true, false)
                        )
                    )
                );
                break;
            case Rockar_FinancingOptions_Model_Adminhtml_System_Config_Source_VariableType::TYPE_DECIMAL:
                $result = number_format($value, 2);
                break;
            case Rockar_FinancingOptions_Model_Adminhtml_System_Config_Source_VariableType::TYPE_STRING:
                $result = $value;
                break;
            case Rockar_FinancingOptions_Model_Adminhtml_System_Config_Source_VariableType::TYPE_GENERAL:
            default:
                $result = number_format($value);
                break;
        }

        return $result . $suffix;
    }

    /**
     * Rewrite parent function to include full title for Finance Groups
     * Merge/Split Options by Groups
     *
     * @param Rockar_FinancingOptions_Model_Resource_Options_Collection $optionsCollection
     *
     * @return array
     */
    public function optionsCollectionFilterPerGroup($optionsCollection)
    {
        /**
         * @var $optionsGroups Rockar_FinancingOptions_Model_Resource_Group_Collection
         */
        $optionsGroups = $this->getOptionsGroups();
        $returnOptionsCollection = [];

        foreach ($optionsGroups as $group) {
            $options = $optionsCollection->getItemsByColumnValue('group_id', $group->getId());
            if (!$options) {
                continue;
            }
            /**
             * @var $option  Rockar_FinancingOptions_Model_Options
             */
            $_option = null;

            foreach ($options as $option) {
                $calc = $option->getData('calc');
                if (
                    $option->hasData('finance_data') &&
                    (isset($calc['min_deposit_validation']) &&
                    $calc['min_deposit_validation']['value'])
                ) {
                    $_option = $option;
                    break;
                }
            }
            if (is_null($_option)) {
                $_option = $options[0];
            }

            $_option->setGroupTitle($group->getTitle());
            $_option->setGroupFullTitle($group->getGroupFullTitle());
            $_option->setGroupLabel($group->getGroupLabel());
            $returnOptionsCollection[] = $_option->toArray();
        }

        /**
         * Add HELP Options as static content block
         */
        $staticOptions = Mage::getModel('rockar_financingoptions/options')->getStaticOptionsPerStoreAndCustomerGroup();

        foreach ($staticOptions->getItems() as $helpOption) {
            /**
             * @var Rockar_FinancingOptions_Model_Options $helpOption
             */
            $helpOption->setGroupTitle($helpOption->getTitle());
            $helpOption->setGroupId($helpOption->getGroupId() + $helpOption->getId());
            $returnOptionsCollection[] = $helpOption->toArray();
        }

        return $returnOptionsCollection;
    }
}

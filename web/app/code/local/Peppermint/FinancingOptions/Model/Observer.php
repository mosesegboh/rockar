<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_FinancingOptions_Model_Observer extends Rockar2_FinancingOptions_Model_Observer
{
    /**
     * Session px value cache
     * @var array
     */
    protected $_pxSessionCache = [];

    /**
     * Cache loaded collections items with same params to improve performance
     * @var array
     */
    protected $_collectionCache = [];

    /**
     * Cache processed product with equal items with calculated
     * @var array
     */
    protected $_processedItems = [];

    /**
     * Callback after Finance data grid columns preparation.
     *
     * @param Varien_Event_Observer $observer
     * @return Peppermint_FinancingOptions_Model_Observer
     */
    public function onAfterDataGridPrepareColumns(Varien_Event_Observer $observer)
    {
        /** @var Rockar_FinancingOptions_Block_Adminhtml_Options_Edit_Tab_Data $block */
        $block = $observer->getData('block');
        $block->getColumn('capid')
            ->setHeader($block->__('Asset ID'));

        return $this;
    }

    /**
     * Callback after Finance data grid columns preparation.
     *
     * @param Varien_Event_Observer $observer
     * @return Peppermint_FinancingOptions_Model_Observer
     */
    public function addExtraColumns(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('financing_options');

        /** @var Rockar_FinancingOptions_Block_Adminhtml_Options_Terms_Edit_Form $fieldset */
        $fieldset = $observer->getData('fieldset');

        $fieldset->addField(
            'individual_fee_monthly',
            'text',
            [
                'name' => 'individual_fee_monthly',
                'label' => $helper->__('Individual Fee Monthly'),
                'title' => $helper->__('Individual Fee Monthly'),
                'class' => 'validate-not-negative-number'
            ]
        );

        $fieldset->addField(
            'individual_fee_capitalised',
            'text',
            [
                'name' => 'individual_fee_capitalised',
                'label' => $helper->__('Individual Fee Capitalised'),
                'title' => $helper->__('Individual Fee Capitalised'),
                'class' => 'validate-not-negative-number'
            ]
        );

        $fieldset->addField(
            'corporate_fee_monthly',
            'text',
            [
                'name' => 'corporate_fee_monthly',
                'label' => $helper->__('Corporate Fee Monthly'),
                'title' => $helper->__('Corporate Fee Monthly'),
                'class' => 'validate-not-negative-number'
            ]
        );

        $fieldset->addField(
            'corporate_fee_capitalised',
            'text',
            [
                'name' => 'corporate_fee_capitalised',
                'label' => $helper->__('Corporate Fee Capitalised'),
                'title' => $helper->__('Corporate Fee Capitalised'),
                'class' => 'validate-not-negative-number'
            ]
        );

        return $this;
    }

    /**
     * Extending the info tab edit form by adding value_cap_limit and percentage_of_vehicle_finance fields.
     *
     * @param Varien_Event_Observer $observer
     * @return Peppermint_FinancingOptions_Model_Observer
     */
    public function onAfterPrepareInfoTabForm(Varien_Event_Observer $observer)
    {
        /** @var Rockar_FinancingOptions_Block_Adminhtml_Options_Edit_Tab_Info $fieldset */
        $fieldset = $observer->getData('fieldset');

        $fieldset->addField(
            'value_cap_limit',
            'text',
            [
                'name' => 'value_cap_limit',
                'label' => __('Value cap limit'),
                'title' => __('Value cap limit')
            ]
        );

        $fieldset->addField(
            'percentage_of_vehicle_finance',
            'text',
            [
                'name' => 'percentage_of_vehicle_finance',
                'label' => __('Percentage of vehicle finance'),
                'title' => __('Percentage of vehicle finance')
            ]
        );

        $fieldset->addField(
            'is_credit',
            'select',
            [
                'label' => 'Has credit element',
                'title' => 'Has credit element',
                'class' => 'required-entry',
                'required' => false,
                'name' => 'is_credit',
                'note' => null,
                'values' => [
                    [
                        'value' => '0',
                        'label' => 'Non-credit (eg. Cash, Rental, Finance with other banks etc.)'
                    ],
                    [
                        'value' => '1',
                        'label' => 'Credit (eg. BMW Select, Instalment Sale etc.)'
                    ]
                ]
            ]
        );

        $fieldset->addField(
            'interest_rate_calc',
            'textarea',
            [
                'name' => 'interest_rate_calc',
                'label' => __('Calculated Interest Rate'),
                'title' => __('Calculated Interest Rate'),
                'note' => __('<p style="color:red;">Important! PHP code needs to be inserted into this field. Please be careful when changing data.</p>
In calculation can be used predefined variables: <br>
1) $subventionAmount <br>
2) $interestRate <br>
3) $subventionRate <br>
Example: $result = $subventionAmount ? $subventionRate : $interestRate;')
            ],
            'interest_charges_calc'
        );

        return $this;
    }

    /**
     * Load and assign finance data with monthly price per every product.
     *
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection
     * @param boolean $isHirePayment
     * @param mixed $fullDeposit
     * @param array $entityIdsToBeDeleted
     *
     * @throws Mage_Core_Exception
     * @throws Zend_Db_Select_Exception
     * @return $this
     */
    protected function _catalogProductCollectionAddMonthlyPrice($collection, $isHirePayment = false, $fullDeposit = null, & $entityIdsToBeDeleted = null)
    {
        $_collection = $this->_cloneCollection($collection);

        Mage::dispatchEvent(
            'rockar_financing_options_product_collection_add_monthly_price_before',
            [
                'collection' => $_collection
            ]
        );

        $config = Mage::helper('financing_options/config');

        Mage::helper('peppermint_financingoptions/cache')->enableCache();

        $financeDataParams = $config->getAllSliderState(false);
        $financeDataParams = $financeDataParams[$this->_activeGroup->getId()] ?? $financeDataParams;
        $monthlyPayAccuracy = $config->getMonthlyPayAccuracyConfig($this->_activeGroup);

        $collectionHash = md5(serialize([
            $_collection->getSelect()->__toString(),
            $financeDataParams,
            $monthlyPayAccuracy
        ]));

        if (empty($this->_collectionCache[$collectionHash])) {
            $this->_collectionCache[$collectionHash] = [
                'entityIdFilter' => [],
                'monthlyPriceSelect' => [],
                'cashbackValues' => [],
                'cashDepositValues' => []
            ];

            foreach ($_collection->getItems() as $product) {
                /** @var $product Mage_Catalog_Model_Product */
                if (!$product->getData('term_id')) {
                    continue;
                }

                $productId = $product->getId();
                $itemHash = md5(serialize([$productId, $financeDataParams, $monthlyPayAccuracy]));

                if (empty($this->_processedItems[$itemHash])) {
                    $this->_processedItems[$itemHash] = $this->_processProductData(
                        $product,
                        $financeDataParams,
                        $monthlyPayAccuracy
                    );
                }

                foreach ($this->_processedItems[$itemHash] as $key => $value) {
                    $this->_collectionCache[$collectionHash][$key][$productId] = $value;
                }
            }
        }

        $entityIdFilter = $this->_collectionCache[$collectionHash]['entityIdFilter'];
        $monthlyPriceSelect = $this->_collectionCache[$collectionHash]['monthlyPriceSelect'];
        $cashbackValues = $this->_collectionCache[$collectionHash]['cashbackValues'];
        $cashDepositValues = $this->_collectionCache[$collectionHash]['cashDepositValues'];

        unset($_collection);

        // Add filter/order by monthly price
        if (!empty($monthlyPriceSelect)) {
            $monthlyCaseStatement = '(CASE ';

            foreach ($monthlyPriceSelect as $entityId => $price) {
                unset($entityIdFilter[$entityId]);
                $monthlyCaseStatement .= " WHEN {{entity_id}} = {$entityId} THEN {$price}";
            }

            $monthlyCaseStatement .= ' END)';
            $collection->addExpressionAttributeToSelect('monthly_price', $monthlyCaseStatement, ['entity_id']);
            $collection->getSelect()->setPart(Varien_Db_Select::ORDER, null);
            $collection->getSelect()->order('monthly_price ASC');
        }

        if (!empty($entityIdFilter)) {
            if(!is_null($entityIdsToBeDeleted)) {
                $entityIdsToBeDeleted = $entityIdFilter;
            }

            $collection->getSelect()->where('e.entity_id NOT IN (' . implode(',', array_keys($entityIdFilter)) . ')');
            unset($entityIdFilter);
        }

        // Add into select cashback price
        if (!empty($cashbackValues)) {
            $cashbackCaseStatement = '(CASE ';

            foreach ($cashbackValues as $entityId => $price) {
                $cashbackCaseStatement .= " WHEN {{entity_id}} = {$entityId} THEN {$price}";
            }

            $cashbackCaseStatement .= ' END)';
            $collection->addExpressionAttributeToSelect('cashback', $cashbackCaseStatement, ['entity_id']);
        }

        // Add into select cashDeposit price
        if (!empty($cashDepositValues)) {
            $cashDepositCaseStatement = '(CASE ';

            foreach ($cashDepositValues as $entityId => $price) {
                $cashDepositCaseStatement .= " WHEN {{entity_id}} = {$entityId} THEN {$price}";
            }

            $cashDepositCaseStatement .= ' END)';
            $collection->addExpressionAttributeToSelect('cash_deposit', $cashDepositCaseStatement, ['entity_id']);
        }

        Mage::dispatchEvent(
            'rockar_catalog_product_list_collection_prepare',
            [
                'collection' => $collection,
                'group_id' => $this->_activeGroup->getId()
            ]
        );

        Mage::helper('peppermint_financingoptions/cache')->clear();
        Mage::helper('peppermint_financingoptions/cache')->disableCache();

        return $this;
    }

    /**
     * Calculated financing data for the product
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $financeDataParams
     * @param array $monthlyPayAccuracy
     */
    protected function _processProductData($product, $financeDataParams, $monthlyPayAccuracy)
    {
        $result = ['entityIdFilter' => 1];

        $financeData = new Varien_Object();
        $option = Mage::getModel('rockar_financingoptions/options');

        $financeData->setData([
            'id' => $product->getData('data_id'),
            'data_id' => $product->getData('data_id'),
            'custom_cash_deposit_minimum' => $product->getData('term_custom_cash_deposit_minimum'),
            'custom_cash_deposit_maximum' => $product->getData('term_custom_cash_deposit_maximum'),
            'finance_facility_fee' => $product->getData('term_finance_facility_fee'),
            'purchase_fee' => $product->getData('term_purchase_fee'),
            'representative_apr' => $product->getData('term_representative_apr'),
            'interest_rate' => $product->getData('term_interest_rate'),
            'mileage_over_term_of_contract' => $product->getData('term_mileage_over_term_of_contract'),
            'excess_mileage_charge' => $product->getData('term_excess_mileage_charge'),
            'annual_mileage' => $product->getData('data_annual_mileage'),
            'manufacture_deposit' => $product->getData('data_manufacture_deposit'),
            'dealer_deposit' => $product->getData('data_dealer_deposit'),
            'optional_final_payment' => $product->getData('data_optional_final_payment'),
            'lease_rental' => $product->getData('data_lease_rental'),
            'payment_plan' => $product->getData('data_payment_plan'),
            'option_rental_price_pr1' => $product->getData('data_option_rental_price_pr1'),
            'service_rental' => $product->getData('data_service_rental'),
            'met_paint_rrp' => $product->getData('data_met_paint_rrp'),
            'met_paint_rental' => $product->getData('data_met_paint_rental'),
            'premium_paint_rrp' => $product->getData('data_premium_paint_rrp'),
            'premium_paint_rental' => $product->getData('data_premium_paint_rental'),
            'ultra_metallic_paint_rrp' => $product->getData('data_ultra_metallic_paint_rrp'),
            'ultimate_metallic_paint_rental' => $product->getData('data_ultimate_metallic_paint_rental'),
            'special_paints_rrp' => $product->getData('data_special_paints_rrp'),
            'special_paints_rental' => $product->getData('data_special_paints_rental'),
            'p11d' => $product->getData('data_p11d'),
            'options_price' => $product->getOptionsPrice(),
            'car_finder_request' => true,
            'rate_subvention_type' => $product->getData('data_rate_subvention_type'),
            'rate_subvention_value' => $product->getData('data_rate_subvention_value'),
            'max_balloon_percent' => $product->getData('data_max_balloon_percent')
        ]);

        $option->setData([
            'options_id' => $product->getData('options_id'),
            'type' => $product->getData('option_type'),
            'method_type' => $product->getData('option_method_type'),
            'minimum_amount_of_finance' => $product->getData('option_minimum_amount_of_finance'),
            'monthly_price_calc' => $product->getData('option_monthly_price_calc'),
            'total_amount_payable_calc' => $product->getData('option_total_amount_payable_calc'),
            'interest_charges_calc' => $product->getData('option_interest_charges_calc'),
            'interest_rate_calc' => $product->getData('option_interest_rate_calc'),
            'finance_data' => $financeData
        ]);

        $product->setCustomPrice($this->_getProductCustomPrice($product));
        $calculationModel = Mage::getModel('rockar_financingoptions/calculation_carfinder');
        $calculationModel->setOption($option);
        $calculationModel->setProduct($product);
        $calculationModel->setParams(
            $financeDataParams['term'],
            $financeDataParams['mileage'],
            $financeDataParams['deposit'],
            $financeDataParams['depositMultiple'],
            $financeDataParams['balloonPercentage']
        );

        if ($calculationModel->getMaxDepositValidation()) {
            $monthlyCost = $calculationModel->getMonthlyCost();

            if (($financeDataParams['monthlypay'] - $monthlyPayAccuracy[0]) <= $monthlyCost
                && ($financeDataParams['monthlypay'] + $monthlyPayAccuracy[1]) >= $monthlyCost
            ) {
                $result['monthlyPriceSelect'] = $monthlyCost;
                $result['cashbackValues'] = $calculationModel->getCashback();
                $result['cashDepositValues'] = $calculationModel->getCashDeposit();
            }
        }

        return $result;
    }

    /**
     * Get custom price for product, to be used in monthly price calculations.
     *
     * @param $product Mage_Catalog_Model_Product
     *
     * @return mixed
     */
    protected function _getProductCustomPrice($product)
    {
        if ($product->getTypeId() === Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
            return $product->getFinalPrice();
        }

        return $product->getFinalPrice() - $product->getOptionsPrice();
    }

    /**
     * Add left join to the product collections.
     *
     * @param Varien_Event_Observer $observer
     * @param Mage_Catalog_Model_Resource_Product_Collection $productsCollection
     *
     * @throws Mage_Core_Exception
     *
     * @throws Mage_Core_Model_Store_Exception
     *
     * @throws Zend_Db_Select_Exception
     *
     * @return $this
     */
    public function catalogProductCollectionAppend(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        /** @var Mage_Catalog_Model_Resource_Product_Collection */
        $collection = $event->getData('collection');

        $this->processCatalogProductCollection($collection);

        return $this;
    }

    /**
     * Processes passed collection
     *
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection
     * @param array $entityIdsToBeDeleted
     *
     * @return $this
     */
    public function processCatalogProductCollection(Mage_Catalog_Model_Resource_Product_Collection $collection, & $entityIdsToBeDeleted = null)
    {
        if ((get_class($collection) != 'Mage_Catalog_Model_Resource_Product_Collection'
                && get_class($collection) != 'Rockar_Catalog_Model_Resource_Product_Collection')
            || empty($collection->getLimitationFilters())
            || !$this->_validateFinalPriceColumn($collection)
        ) {
            return $this;
        }

        $helper = Mage::helper('financing_options');
        $config = Mage::helper('financing_options/config');
        $payInFull = $helper->getDefaultPayInFullPayment();
        $financeFilters = Mage::app()->getRequest()->getParam('financeFilters', []);

        if (!empty($financeFilters) && isset($financeFilters['method'])) {
            $savedFinanceData = $helper->getFinanceData();
            $savedFinanceData[$financeFilters['method']] = $financeFilters;
            $savedFinanceData['method'] = (int) $financeFilters['method'];
            $savedFinanceData['group_id'] = (int) $financeFilters['method'];
            $helper->setFinanceData($savedFinanceData);
        }

        $activePayment = $helper->getActivePayment();

        if (empty($activePayment)) {
            // No finance products are available for current configuration, nothing to do further
            return $this;
        }

        $this->_activeGroup = Mage::getModel('rockar_financingoptions/group')->load($activePayment['group_id']);

        $financeDataParams = $config->getAllSliderState(false, $this->_activeGroup);
        $financeDataParams = $financeDataParams[$activePayment['group_id']] ?? $financeDataParams;
        $financeDataParams['full_deposit'] = array_sum([
            $financeDataParams['deposit'],
            $this->_getPartExchangeValue(),
            $this->_getOutstandingFinance() * -1
        ]);

        // Dispatching event here, because need to force resort, since only now finance is correctly saved in session
        Mage::dispatchEvent(
            'rockar_catalog_product_list_collection_prepare',
            [
                'collection' => $collection,
                'group_id' => $this->_activeGroup->getId()
            ]
        );

        if (in_array($activePayment['group_id'], array_column($payInFull, 'group_id'))) {
            $payInFullMin = (int) ($financeDataParams['payinfull'][0]);
            $payInFullMax = (int) ($financeDataParams['payinfull'][1]);

            $collection->getSelect()
                ->where("final_price BETWEEN {$payInFullMin} AND {$payInFullMax}");

            return $this;
        }

        $isHirePayment = $helper->isHirePaymentGroup($activePayment['group_id']);

        $resource = Mage::getSingleton('core/resource');
        $financeDataTableName = $resource->getTableName('rockar_financingoptions/data');
        $financeDataProductsTableName = $resource->getTableName('rockar_financingoptions/data_products');
        $financeGroupTableName = $resource->getTableName('rockar_financingoptions/group');
        $financeTermsTableName = $resource->getTableName('rockar_financingoptions/terms');
        $parentProductTableName = $resource->getTableName('catalog/product_super_link');

        /** @var Rockar_FinancingOptions_Model_Resource_Options_Collection $financeOptionsSelect */
        $financeOptionsSelect = Mage::getModel('rockar_financingoptions/options')->getCollection()
            ->addStoreFilter(Mage::app()->getStore())
            ->addCustomerGroupFilter($this->getCustomerGroupId())
            ->addFieldToFilter('is_active', 1)
            ->addOrder('position', Varien_Data_Collection::SORT_ORDER_ASC)
            ->addFieldToFilter('group_id', $activePayment['group_id']);

        Mage::dispatchEvent(
            'rockar_financing_options_finance_options_select_after',
            [
                'finance_options_select' => $financeOptionsSelect,
                'collection' => $collection
            ]
        );

        $financeOptionsSelect = $financeOptionsSelect->load()->getSelect();

        $collection->getSelect()
            ->joinLeft(
                ['parent' => $parentProductTableName],
                'parent.product_id = e.entity_id',
                ['parent.parent_id']
            )
            ->joinLeft(
                ['finance_data_products' => $financeDataProductsTableName],
                'finance_data_products.product_id = e.entity_id',
                ['finance_data_products.data_id']
            )
            ->joinLeft(
                ['finance_data' => $financeDataTableName],
                'finance_data_products.data_id = finance_data.data_id'
                . " && finance_data.term = '{$financeDataParams['term']}'"
                . sprintf(
                    ' && finance_data.annual_mileage IN (%s)',
                    implode(',', [0, $financeDataParams['mileage']])
                ),
                [
                    'data_annual_mileage' => 'finance_data.annual_mileage',
                    'data_manufacture_deposit' => 'finance_data.manufacture_deposit',
                    'data_dealer_deposit' => 'finance_data.dealer_deposit',
                    'data_optional_final_payment' => 'finance_data.optional_final_payment',
                    'data_lease_rental' => 'finance_data.lease_rental',
                    'data_payment_plan' => 'finance_data.payment_plan',
                    'data_option_rental_price_pr1' => 'finance_data.option_rental_price_pr1',
                    'data_service_rental' => 'finance_data.service_rental',
                    'data_met_paint_rrp' => 'finance_data.met_paint_rrp',
                    'data_met_paint_rental' => 'finance_data.met_paint_rental',
                    'data_premium_paint_rrp' => 'finance_data.premium_paint_rrp',
                    'data_premium_paint_rental' => 'finance_data.premium_paint_rental',
                    'data_ultra_metallic_paint_rrp' => 'finance_data.ultra_metallic_paint_rrp',
                    'data_ultimate_metallic_paint_rental' => 'finance_data.ultimate_metallic_paint_rental',
                    'data_special_paints_rrp' => 'finance_data.special_paints_rrp',
                    'data_special_paints_rental' => 'finance_data.special_paints_rental',
                    'data_p11d' => 'finance_data.p11d',
                    'data_rate_subvention_type' => 'finance_data.rate_subvention_type',
                    'data_rate_subvention_value' => 'finance_data.rate_subvention_value',
                    'data_max_balloon_percent' => 'finance_data.max_balloon_percent'
                ]
            )
            ->joinLeft(
                ['finance_options' => $financeOptionsSelect],
                'finance_data.payment_type = finance_options.type',
                ['finance_options.options_id',
                    'option_type' => 'finance_options.type',
                    'option_method_type' => 'finance_options.method_type',
                    'option_minimum_amount_of_finance' => 'finance_options.minimum_amount_of_finance',
                    'option_monthly_price_calc' => 'finance_options.monthly_price_calc',
                    'option_total_amount_payable_calc' => 'finance_options.total_amount_payable_calc',
                    'option_interest_charges_calc' => 'finance_options.interest_charges_calc',
                    'option_interest_rate_calc' => 'finance_options.interest_rate_calc'
                ]
            )
            ->joinLeft(
                ['finance_group' => $financeGroupTableName],
                "finance_group.group_id = finance_options.group_id && finance_group.group_id
                = '{$activePayment['group_id']}'"
            )
            ->joinLeft(
                ['finance_options_terms' => $financeTermsTableName],
                'finance_options_terms.option_id = finance_options.options_id AND finance_options_terms.term
                = finance_data.term',
                [
                    'term_id' => 'finance_options_terms.term_id',
                    'term' => 'finance_options_terms.term',
                    'term_custom_cash_deposit_minimum' => 'finance_options_terms.custom_cash_deposit_minimum',
                    'term_custom_cash_deposit_maximum' => 'finance_options_terms.custom_cash_deposit_maximum',
                    'term_finance_facility_fee' => 'finance_options_terms.finance_facility_fee',
                    'term_purchase_fee' => 'finance_options_terms.purchase_fee',
                    'term_representative_apr' => 'finance_options_terms.representative_apr',
                    'term_interest_rate' => 'finance_options_terms.interest_rate',
                    'term_mileage_over_term_of_contract' => 'finance_options_terms.mileage_over_term_of_contract',
                    'term_excess_mileage_charge' => 'finance_options_terms.excess_mileage_charge'
                ]
            )
            ->group('e.entity_id');

        Mage::dispatchEvent(
            'rockar_catalog_product_list_collection_post',
            [
                'collection' => $collection
            ]
        );

        if ($isHirePayment) {// If Hire Payment
            $collection->getSelect()
                ->where('finance_data.payment_plan = ?', (int) $financeDataParams['depositMultiple'])
                ->where('finance_options.maintenance = ?', (int) $financeDataParams['maintenance']);
            $collection->addExpressionAttributeToSelect('is_hire', 'SUM(1)', ['entity_id']);
        } else { // If not Hire Payment and not PayInFull
            $collection->getSelect()
                ->where( // Min Deposit Validation
                    '(price_index.final_price / 100 * finance_options_terms.custom_cash_deposit_minimum
                    - finance_data.manufacture_deposit - finance_data.dealer_deposit) < ?',
                    $financeDataParams['full_deposit']
                );
        }

        $this->_catalogProductCollectionAddMonthlyPrice($collection, false, null, $entityIdsToBeDeleted);

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function getObserverCustomerType(Varien_Event_Observer $observer)
    {
        $requestData = $observer->getRequestData();
        Mage::getSingleton('customer/session')->setCustomerType($requestData['customer_type']);

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function setObserverCustomerTypeByIsCorporateParam(Varien_Event_Observer $observer)
    {
        $isCorporate = Mage::app()->getRequest()->getParam('isCorporate');

        if (in_array($isCorporate, ['1', '0'])) {
            $customerType = $isCorporate ? 'business' : 'individual';
            Mage::getSingleton('customer/session')->setCustomerType($customerType);
        }

        return $this;
    }


    /**
     * Attaches finance data to imported products
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     * @throws Exception
     */
    public function attachFinancesToProducts(Varien_Event_Observer $observer)
    {
        $productIds = $observer->getEvent()->getProductIds();

        if ($productIds) {
            Mage::helper('peppermint_financingoptions/product_map')->mapFinancesToProducts($productIds);
        }

        return $this;
    }

    /**
     * Attaches finance data to the saved option
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     * @throws Exception
     */
    public function attachFinancesToOptionOnSave(Varien_Event_Observer $observer)
    {
        $option = $observer->getEvent()->getOption();
        $origType = $option->getOrigData('type');
        $type = $option->getData('type');

        if ($origType !== $type) {
            $table = Mage::getModel('rockar_financingoptions/data')->getResource()
                ->getMainTable();
            $write = Mage::getSingleton('core/resource')->getConnection('core_write');
            $write->beginTransaction();

            try {
                $write->update(
                    $table,
                    ['payment_type' => $type],
                    $write->quoteInto('payment_type = ?', $origType)
                );

                $write->commit();
            } catch (Exception $e) {
                $write->rollback();

                // Throw this error further to allow admin user see,
                // that we faced a problem during options saving
                throw ($e);
            }
        }

        return $this;
    }

    /**
     * Returns part exchange value.
     *
     * @return float
     */
    protected function _getPartExchangeValue()
    {
        $cacheKey = 'px_value';

        if (!isset($this->_pxSessionCache[$cacheKey])) {
            $this->_pxSessionCache[$cacheKey] = (float) $this->_getPartExchange()->getData('part_exchange_value');
        }

        return $this->_pxSessionCache[$cacheKey];
    }

    /**
     * Returns outstanding finance value
     *
     * @return float
     */
    protected function _getOutstandingFinance()
    {
        $cacheKey = 'outstanding_finance';

        if (!isset($this->_pxSessionCache[$cacheKey])) {
            $this->_pxSessionCache[$cacheKey] = (float) $this->_getPartExchange()->getData('outstanding_finance');
        }

        return $this->_pxSessionCache[$cacheKey];
    }

    /**
     * Update simple quote item as well, when configurable product item gets updated on checkout
     *
     * @param $observer
     */
    public function updateSimpleQuoteItemOnCheckout($observer)
    {
        $quoteItem = $observer->getEvent()->getData('item');
        $quote = Mage::getSingleton('checkout/cart')->getQuote();
        $simpleQuoteItem = Mage::helper('rockar_checkout/quote')->getFirstVisibleQuoteItem($quote);
        $simpleQuoteItem->setData('finance_data', $quoteItem->getData('finance_data'));
        $simpleQuoteItem->setData('finance_data_variables', $quoteItem->getData('finance_data_variables'));
        $simpleQuoteItem->setData('finance_overlay', $quoteItem->getData('finance_overlay'));
        $simpleQuoteItem->save();
    }
}

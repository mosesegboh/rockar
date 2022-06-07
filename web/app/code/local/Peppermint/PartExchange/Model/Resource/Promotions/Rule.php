<?php
/**
 * @category  Peppermint
 * @package   Peppermint_PartExchange
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_PartExchange_Model_Resource_Promotions_Rule extends Rockar_PartExchange_Model_Resource_Promotions_Rule
{

    const PARTEXCHANGE_PROMOTIONRULE_PRODUCT_TABLE = 'peppermint_partexchange_promotionrule_product';
    const PARTEXCHANGE_PROMOTIONRULE_PRODUCT_PRICE_TABLE = 'peppermint_partexchange_promotionrule_product_price';

    /**
     * Primary key auto increment flag
     *
     * @var bool
     */
    protected $_isPkAutoIncrement = false;

    /**
     * Factory instance
     *
     * @var Mage_Core_Model_Factory
     */
    protected $_factory;

    /**
     * App instance
     *
     * @var Mage_Core_Model_App
     */
    protected $_app;

    /**
     * Constructor with parameters
     * Array of arguments with keys
     *  - 'factory' Mage_Core_Model_Factory
     *
     * @param array $args
     */
    public function __construct(array $args = [])
    {
        $this->_factory = $args['factory'] ?? Mage::getSingleton('core/factory');
        $this->_app = $args['app'] ?? Mage::app();

        parent::__construct();
    }

    /**
     * Updates rule product data table. Used fro reports only, not for real calculations
     *
     * @param Rockar_PartExchange_Model_Promotions_Rule $rule
     * @return $this
     * @throws Exception
     */
    public function updateRuleProductData(Rockar_PartExchange_Model_Promotions_Rule $rule)
    {
        $ruleId = $rule->getId();
        $write = $this->_getWriteAdapter();
        $this->cleanProductData($ruleId, $rule->getProductsFilter() ?: []); // will remove all rule_products for the rule !

        if (!$rule->getIsActive()) {
            $write->commit();

            return $this;
        }

        $websiteIds = $rule->getWebsiteIds();

        if (!is_array($websiteIds)) {
            $websiteIds = explode(',', $websiteIds);
        }

        $demoWebsiteId = Mage::helper('peppermint_all/store')->getDemoWebSiteId();
        $websiteIds = array_filter($websiteIds, function ($websiteId) use ($demoWebsiteId) {
            return $websiteId !== $demoWebsiteId;
        });

        if (empty($websiteIds)) {
            return $this;
        }

        try {
            $this->insertRuleData($rule, $websiteIds); // here magic!
            $write->commit();
        } catch (Exception $e) {
            $write->rollback();
            throw $e;
        }

        return $this;
    }

    /**
     * Deletes records in peppermint_partexchange_promotionrule_product by rule ID and product IDs
     *
     * @param int $ruleId
     * @param array $productIds
     */
    public function cleanProductData($ruleId, $productIds = [])
    {
        /** @var $write Varien_Db_Adapter_Interface */
        $write = $this->_getWriteAdapter();

        $conditions = ['rule_id = ?' => $ruleId];

        if (count($productIds) > 0) {
            $conditions['product_id IN (?)'] = $productIds;
        }

        $write->delete(static::PARTEXCHANGE_PROMOTIONRULE_PRODUCT_TABLE, $conditions);
    }

    /**
     * Inserts rule data into peppermint_partexchange_promotionrule_product
     *
     * @param Rockar_PartExchange_Model_Promotions_Rule $rule
     * @param array $websiteIds
     * @param array $productIds
     */
    public function insertRuleData(Rockar_PartExchange_Model_Promotions_Rule $rule, array $websiteIds, array $productIds = [])
    {
        /** @var $write Varien_Db_Adapter_Interface */
        $write = $this->_getWriteAdapter();

        $customerGroupIds = $rule->getCustomerGroupIds();
        $fromTime = (int) strtotime($rule->getFromDate());
        $toTime = (int) strtotime($rule->getToDate());
        $toTime = $toTime ? ($toTime + Mage_CatalogRule_Model_Resource_Rule::SECONDS_IN_DAY - 1) : 0;
        /** @var Mage_Core_Model_Date $coreDate */
        $coreDate = $this->_factory->getModel('core/date');
        $timestamp = $coreDate->gmtTimestamp('Today');

        if ($fromTime > $timestamp || ($toTime && $toTime < $timestamp)) {
            return;
        }

        $sortOrder = (int) $rule->getSortOrder();
        $actionOperator = $rule->getSimpleAction();
        $actionAmount = (float) $rule->getDiscountAmount();
        $subActionOperator = $rule->getSubIsEnable() ? $rule->getSubSimpleAction() : '';
        $subActionAmount = (float) $rule->getSubDiscountAmount();
        $actionStop = (int) $rule->getStopRulesProcessing();
        /** @var $helper Mage_Catalog_Helper_Product_Flat */
        $helper = $this->_factory->getHelper('catalog/product_flat');

        if ($helper->isEnabled() && $helper->isBuiltAllStores()) {
            /** @var $store Mage_Core_Model_Store */
            foreach ($this->_app->getStores(false) as $store) {
                if (in_array($store->getWebsiteId(), $websiteIds)) {
                    /** @var $selectByStore Varien_Db_Select */
                    $selectByStore = $rule->getProductFlatSelect($store->getId())
                        ->joinLeft(['cg' => $this->getTable('customer/customer_group')],
                            $write->quoteInto('cg.customer_group_id IN (?)', $customerGroupIds),
                            ['cg.customer_group_id'])
                        ->reset(Varien_Db_Select::COLUMNS)
                        ->columns([
                            new Zend_Db_Expr($store->getWebsiteId()),
                            'cg.customer_group_id',
                            'p.entity_id',
                            'cpf.price',
                            new Zend_Db_Expr($rule->getId()),
                            new Zend_Db_Expr($fromTime),
                            new Zend_Db_Expr($toTime),
                            new Zend_Db_Expr("'" . $actionOperator . "'"),
                            new Zend_Db_Expr($actionAmount),
                            new Zend_Db_Expr($actionStop),
                            new Zend_Db_Expr($sortOrder)
                        ]);

                    if (count($productIds) > 0) {
                        $selectByStore->where('p.entity_id IN (?)', array_keys($productIds));
                    }

                    $selects = $write->selectsByRange('entity_id', $selectByStore, Mage_CatalogRule_Model_Resource_Rule::RANGE_PRODUCT_STEP);

                    foreach ($selects as $select) {
                        $write->query(
                            $write->insertFromSelect(
                                $select, static::PARTEXCHANGE_PROMOTIONRULE_PRODUCT_TABLE, [
                                'website_id',
                                'customer_group_id',
                                'product_id',
                                'product_price',
                                'rule_id',
                                'from_time',
                                'to_time',
                                'action_operator',
                                'action_amount',
                                'action_stop',
                                'sort_order'
                            ], Varien_Db_Adapter_Interface::INSERT_IGNORE
                            )
                        );
                    }
                }
            }
        } else {
            if (count($productIds) == 0) {
                Varien_Profiler::start('__MATCH_PRODUCTS__');
                $productIds = $rule->getMatchingProductIds();
                Varien_Profiler::stop('__MATCH_PRODUCTS__');
            }

            $rows = [];
            foreach ($productIds as $productId => $validationByWebsite) {
                foreach ($websiteIds as $websiteId) {
                    foreach ($customerGroupIds as $customerGroupId) {
                        if (empty($validationByWebsite[$websiteId])) {
                            continue;
                        }
                        $rows[] = [
                            'rule_id' => $rule->getId(),
                            'from_time' => $fromTime,
                            'to_time' => $toTime,
                            'website_id' => $websiteId,
                            'customer_group_id' => $customerGroupId,
                            'product_id' => $productId,
                            'action_operator' => $actionOperator,
                            'action_amount' => $actionAmount,
                            'action_stop' => $actionStop,
                            'sort_order' => $sortOrder,
                            'sub_simple_action' => $subActionOperator,
                            'sub_discount_amount' => $subActionAmount
                        ];

                        if (count($rows) == 1000) {
                            $write->insertMultiple(static::PARTEXCHANGE_PROMOTIONRULE_PRODUCT_TABLE, $rows);
                            $rows = [];
                        }
                    }
                }
            }

            if (!empty($rows)) {
                $write->insertMultiple(static::PARTEXCHANGE_PROMOTIONRULE_PRODUCT_TABLE, $rows);
            }
        }
    }

    /**
     * Overriden to substitute flat table with idx table
     *
     * {@inheritDoc}
     */
    public function getProductFlatSelect($storeId, $condition)
    {
        $select = $this->_getReadAdapter()->select();
        $select->from(
            ['p' => $this->getTable('catalog/product')],
            [new Zend_Db_Expr('DISTINCT p.entity_id')]
        )
            ->joinInner(
                ['cpf' => $this->_factory->getHelper('peppermint_catalogrule')->getFlatForRuleIdxTablePrefix() . $storeId],
                'cpf.entity_id = p.entity_id',
                []
            )->joinLeft(
                ['ccp' => $this->getTable('catalog/category_product')],
                'ccp.product_id = p.entity_id',
                []
            )->where('p.type_id = ?', Mage_Catalog_Model_Product_Type::TYPE_SIMPLE);

        $where = $condition->prepareConditionSql();

        if (!empty($where)) {
            $select->where($where);
        }

        return $select;
    }
}

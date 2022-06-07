<?php
/**
 * @category  Peppermint
 * @package   Peppermint_CatalogRule
 * @author    Juris Krislauks <techteam@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_CatalogRule_Helper_Data extends Rockar_CatalogRule_Helper_Data
{
    /**
     * Group price update cron job code
     */
    const GROUP_PRICE_UPDATE_CRON_JOB = 'peppermint_group_price_recompile';

    /**
     * Product count limit for apply all rules
     */
    const APPLY_ALL_PRODUCT_COUNT_LIMIT = 90;

    /**
     * Attribute codes that will be comparable based on today's date difference.
     */
    protected const COMPARABLE_DATE_ATTRIBUTES = ['published_to_ds_date', 'rfs_date'];

    /**
     * Flat for catalog rules idx table prefix
     */
    protected const FLAT_FOR_RULE_IDX_TABLE_PREFIX = 'flat_products_for_rules_idx_';

    /**
     * catalog_product_entity columns to select for rules idx tables
     */
    protected const CATALOG_PRODUCT_ENTITY_COLUMNS_TO_SELECT = ['entity_id', 'type_id', 'attribute_set_id'];

    /**
     * Getter for COMPARABLE_DATE_ATTRIBUTES.
     *
     * @return array
     */
    public function getComparableDateAttributes()
    {
        return static::COMPARABLE_DATE_ATTRIBUTES;
    }

    /**
     * Getter for flat catalog rules idx table prefix
     *
     * @return string
     */
    public function getFlatForRuleIdxTablePrefix()
    {
        return static::FLAT_FOR_RULE_IDX_TABLE_PREFIX;
    }

    /**
     * Update catalog rules data and grouPrice for array of product Ids
     *
     * @param array $productIds
     * @param bool $runPriceIndex
     * @return void|$this
     */
    public function applyRulesProductData(array $productIds)
    {
        if (empty($productIds)) {
            return $this;
        }
        // It is quicker to apply all rules if there is more than 90 products
        if (count($productIds) >= self::APPLY_ALL_PRODUCT_COUNT_LIMIT) {
            Mage::getModel('catalogrule/rule')->applyAll();

            return $this;
        }

        $this->createProductAttributesForPriceRulesFlatIdx();

        $rules = Mage::getModel('catalogrule/rule')->getCollection()
            ->addFieldToFilter('is_active', 1);
        $resource = Mage::getResourceSingleton('catalogrule/rule');

        foreach ($rules as $rule) {
            // Refresh catalogrule_product table
            $rule->setProductsFilter($productIds);
            $resource->updateRuleProductData($rule);
        }

        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addFieldToFilter('entity_id', ['in' => $productIds]);

        foreach ($collection as $product) {
            // Re-index catalogrule_product_price table
            $resource->applyAllRules($product);
        }
    }

    /**
     * Creates idx tables of all the products with attributes used in catalog rules conditions
     *
     * @throws Zend_Db_Statement_Exception
     */
    public function createProductAttributesForPriceRulesFlatIdx()
    {
        $resource = Mage::getModel('catalogrule/rule')->getResource();
        $connection = $resource->getReadConnection();
        $productTypeSimpleText = Mage_Catalog_Model_Product_Type::TYPE_SIMPLE;

        $attributesSelect = $connection->query('
            SELECT ea.attribute_id, attribute_code, backend_type
            FROM catalog_eav_attribute cea
            JOIN eav_attribute ea ON ea.attribute_id = cea.attribute_id
            WHERE is_used_for_promo_rules = 1
        ');

        $mainColumns = array_map(
            function($column) {
                return '`e`.' .  $column;
            },
            static::CATALOG_PRODUCT_ENTITY_COLUMNS_TO_SELECT
        );

        $atributeData = $attributesSelect->fetchAll();
        $demoStoreCode = Mage::helper('peppermint_all/store')->getDemoStoreCode();
        $idxTableNamePrefix = $this->getFlatForRuleIdxTablePrefix();

        foreach (Mage::app()->getStores() as $storeId => $store) {
            //Skip for demo store
            if ($store->getCode() === $demoStoreCode) {
                continue;
            }

            $statement = [];
            $columns = [];

            foreach ($atributeData as $item) {
                $columns[] = sprintf('`%s`.`value` as `%s`', $item['attribute_code'], $item['attribute_code']);

                $statement[$item['attribute_code']] = sprintf(
                    'LEFT JOIN `catalog_product_entity_%2$s` AS `%1$s`
                    ON (`%1$s`.`entity_id` = `e`.`entity_id`)
                    AND (`%1$s`.`attribute_id` = %3$s)',
                    $item['attribute_code'],
                    $item['backend_type'],
                    $item['attribute_id']
                );
            }

            $tableName = $idxTableNamePrefix . $storeId;

            $connection->query(
                "DROP TABLE IF EXISTS {$tableName}; " .
                sprintf(
                    "CREATE TABLE {$tableName}
                        AS (SELECT %s, %s
                        FROM `catalog_product_entity` AS `e` %s
                        INNER JOIN catalog_product_website product_stores ON e.entity_id = product_stores.product_id
                            AND product_stores.website_id = %s
                    ",
                    implode(', ', $mainColumns),
                    implode(', ', $columns),
                    implode(' ', $statement),
                    $storeId
                )
                . "WHERE e.type_id = '{$productTypeSimpleText}')"
            );
        }
    }

    /**
     * Schedules price reindex
     *
     * @return void
     */
    public function schedulePriceReindex()
    {
        if (!Mage::helper('peppermint_all')->isCronJobAlreadyScheduled(self::GROUP_PRICE_UPDATE_CRON_JOB, 3)) {
            // schedule group price update cron after applyAllRules
            Mage::getModel('cron/schedule')->setJobCode(self::GROUP_PRICE_UPDATE_CRON_JOB)
                ->setScheduledReason(Aoe_Scheduler_Model_Schedule::REASON_SCHEDULENOW_CLI)
                ->schedule()
                ->save();
        }
    }
}

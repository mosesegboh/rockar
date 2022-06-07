<?php
/**
 * @category  Peppermint
 * @package   Peppermint_CatalogRule
 * @author    Jiraphong Witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_CatalogRule_Model_Resource_Rule extends Mage_CatalogRule_Model_Resource_Rule
{
    /**
     * Primary key auto increment flag
     *
     * @var bool
     */
    protected $_isPkAutoIncrement = false;

    /**
     * Overriden to substitute flat table with idx table
     * {@inheritDoc}
     */
    public function getProductFlatSelect($storeId, $condition)
    {
        if ((int) $storeId === (int) $this->_factory->getHelper('peppermint_all/store')->getDemoStoreId()) {
            // don't use flat_products_for_rules_idx_ table for demo store
            return parent::getProductFlatSelect($storeId, $condition);
        }

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

    /**
     * Overriden to send rule product ids to insertRuleData
     * {@inheritDoc}
     */
    public function updateRuleProductData(Mage_CatalogRule_Model_Rule $rule)
    {
        $ruleId = $rule->getId();
        $write = $this->_getWriteAdapter();
        $write->beginTransaction();

        if ($rule->getProductsFilter()) {
            $this->cleanProductData($ruleId, $rule->getProductsFilter());
        } else {
            $this->cleanProductData($ruleId);
        }

        if (!$rule->getIsActive()) {
            $write->commit();

            return $this;
        }

        $websiteIds = $rule->getWebsiteIds();

        if (!is_array($websiteIds)) {
            $websiteIds = explode(',', $websiteIds);
        }

        if (empty($websiteIds)) {
            return $this;
        }

        try {
            $this->insertRuleData(
                $rule,
                $websiteIds,
                $rule->getProductsFilter() ? array_flip($rule->getProductsFilter()) : []
            );
            $write->commit();
        } catch (Exception $e) {
            $write->rollback();
            throw $e;
        }

        return $this;
    }
}

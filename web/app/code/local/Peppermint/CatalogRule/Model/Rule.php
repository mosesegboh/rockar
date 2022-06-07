<?php
/**
 * @category  Peppermint
 * @package   Peppermint_CatalogRule
 * @author    Jiraphong Witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

/**
 * Class Peppermint_CatalogRule_Model_Rule
 */
class Peppermint_CatalogRule_Model_Rule extends Mage_CatalogRule_Model_Rule
{
    /**
     * Apply all price rules, invalidate related cache and refresh price index
     *
     * @return Mage_CatalogRule_Model_Rule
     */
    public function applyAll()
    {
        $params = Mage::app()->getRequest()
            ->getParams();
        $ruleId = $params['rule_id'] ?? false;

        if (($params['is_active'] ?? false) === '0' && $ruleId) {
            $productIds = array_unique(Mage::getResourceSingleton('catalogrule/rule')->getRuleProductIds($ruleId));
        }

        Mage::helper('peppermint_catalogrule')->createProductAttributesForPriceRulesFlatIdx();

        $this->getResourceCollection()
            ->walk([$this->_getResource(), 'updateRuleProductData']);

        $this->_getResource()->applyAllRules();
        $this->_invalidateCache();

        if ($ruleId) {
            Mage::dispatchEvent(
                'peppermint_catalogrule_rule_apply_after',
                [
                    'rule_id' => $ruleId,
                    'product_ids' => $productIds ?? false
                ]
            );
        }

        Mage::getModel('peppermint_catalog/cron')->run();
        // Re-index product price after group price update event above
        $indexProcess = Mage::getSingleton('index/indexer')->getProcessByCode('catalog_product_price');

        if ($indexProcess) {
            $indexProcess->reindexAll();
        }
    }
}

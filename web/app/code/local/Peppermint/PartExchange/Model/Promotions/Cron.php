<?php
/**
 * @category  Peppermint
 * @package   Peppermint_PartExchange
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_PartExchange_Model_Promotions_Cron
{
    /**
     * Fill product-to-rule table
     *
     * @return void
     * @throws Zend_Db_Exception
     */
    public function dailyPxPromptionsUpdate()
    {
        $rules = Mage::getModel('rockar_partexchange/promotions_rule')->getCollection();
        $resource = Mage::getResourceSingleton('rockar_partexchange/promotions_rule');

        foreach ($rules as $rule) {
            $resource->updateRuleProductData($rule);
        }

        $this->calculateFinalDiscounts();
    }

    /**
     * Saves final shortfall allowances for all applicable rules
     *
     * @throws Zend_Db_Exception
     */
    public function calculateFinalDiscounts()
    {
        /** @var Magento_Db_Adapter_Pdo_Mysql $connection */
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        /** @var Magento_Db_Adapter_Pdo_Mysql $writeConnection */
        $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');

        $productIds = $connection->fetchCol(
            $connection->select()
                ->distinct()
                ->from(
                    Peppermint_PartExchange_Model_Resource_Promotions_Rule::PARTEXCHANGE_PROMOTIONRULE_PRODUCT_TABLE,
                    ['product_id']
                )
        );

        $writeConnection->truncateTable(Peppermint_PartExchange_Model_Resource_Promotions_Rule::PARTEXCHANGE_PROMOTIONRULE_PRODUCT_PRICE_TABLE);
        $toInsert = [];

        foreach ($productIds as $productId) {
            $discountArray = $this->calculateProductShortfallAllowance($productId);

            foreach ($discountArray as $website => $cgData) {
                foreach ($cgData as $customerGroupId => $shortfallValue) {
                    $toInsert[] = [
                        'website_id' => $website,
                        'customer_group_id' => $customerGroupId,
                        'product_id' => $productId,
                        'action_amount' => $shortfallValue
                    ];
                }
            }
        }

        $writeConnection->insertMultiple(
            Peppermint_PartExchange_Model_Resource_Promotions_Rule::PARTEXCHANGE_PROMOTIONRULE_PRODUCT_PRICE_TABLE,
            $toInsert
        );
    }

    /**
     * Calculates final shortfall allowances for all applicable rules for the given product
     *
     * @param $productId
     * @return array
     */
    public function calculateProductShortfallAllowance($productId)
    {
        $this->_stopFurtherRules = false;
        $totalDiscount = [];
        $appliedRuleIds = [];
        $discountAmount = 0;

        /** @var Magento_Db_Adapter_Pdo_Mysql $connection */
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $rulesData = $connection->fetchAll(
            $connection->select()
                ->from(Peppermint_PartExchange_Model_Resource_Promotions_Rule::PARTEXCHANGE_PROMOTIONRULE_PRODUCT_TABLE)
                ->where('product_id = ?', $productId)
                ->order('sort_order ' . Varien_Db_Select::SQL_ASC)
        );

        foreach ($rulesData as $ruleData) { // rules by id for product! No, ve're already valid!
            $customerGroupId = $ruleData['customer_group_id'];
            $website_id = $ruleData['website_id'];

            switch ($ruleData['action_operator']) {
                case Mage_SalesRule_Model_Rule::BY_PERCENT_ACTION:
                    $rulePercent = min(100, $ruleData['action_amount']);
                    $discountAmount = $ruleData['price'] * $rulePercent / 100;
                    break;
                case Mage_SalesRule_Model_Rule::BY_FIXED_ACTION:
                    $discountAmount = $ruleData['action_amount'];
                    break;
            }

            $totalDiscount[$website_id][$customerGroupId] = $totalDiscount[$website_id][$customerGroupId] ?? 0;
            $totalDiscount[$website_id][$customerGroupId] += $discountAmount;

            if ($totalDiscount[$website_id][$customerGroupId] > 0) {
                $appliedRuleIds[] = $ruleData['rule_id'];
            }

            if ($ruleData['action_stop']) {
                $this->_stopFurtherRules = true;
                break;
            }
        }

        return $totalDiscount;
    }
}

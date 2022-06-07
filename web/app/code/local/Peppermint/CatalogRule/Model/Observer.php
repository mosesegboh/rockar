<?php
/**
 * @category  Peppermint
 * @package   Peppermint_CatalogRule
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_CatalogRule_Model_Observer extends Rockar_CatalogRule_Model_Observer
{
    /**
     * Apply all price rules for the products
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function applyAllRulesToProducts($observer)
    {
        $productIds = $observer->getEvent()
            ->getProductIds();

        $deletedProductIds = $observer->getEvent()
            ->getDeletedProductIds();

        if (!empty($productIds)) {
            Mage::helper('peppermint_catalogrule')->applyRulesProductData($productIds);
        }

        // Also need to check for product limit here, if limit is reach do not run group price cron and price-index
        // As we run applyAll rules action, when limit is reach, which already runs these two processes
        if (
            (!empty($productIds) && count($productIds) < Peppermint_CatalogRule_Helper_Data::APPLY_ALL_PRODUCT_COUNT_LIMIT)
            || !empty($deletedProductIds)
        ) {
            Mage::getModel('peppermint_catalog/cron')->run();
            $indexerPrice = Mage::getResourceModel('catalog/product_indexer_price');
            $indexerPrice->reindexAll();
        }

        return $this;
    }

    /**
     * Resetting website Ids form fields to no include demo store
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function prepareMainEditForm(Varien_Event_Observer $observer)
    {
        $websiteIdsFieldset = $observer->getForm()
            ->getElement('website_ids');

        if ($websiteIdsFieldset) {
            $websiteIdsFieldset->setValues(
                Mage::getSingleton('peppermint_catalogrule/system_config_source_websiteIds')->toOptionArray()
            );
        }
    }

    /**
     * Create pricing rule snapshot
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function setPricingRules(Varien_Event_Observer $observer)
    {
        $allHelper = Mage::helper('rockar_all');
        $amendHelper = Mage::helper('rockar_orderamend');
        $catalogueRuleHelper = Mage::helper('rockar_orderamend/catalogRule');
        $orderamendOrderHelper = Mage::helper('rockar_orderamend/order');
        $checkoutOrderHelper = Mage::helper('rockar_checkout/order');
        $orderId = $observer->getOrder()
            ->getId();

        /**
         * @var Mage_Sales_Model_Order $order
         */
        $order = Mage::getModel('sales/order')->load($orderId);
        $product = $checkoutOrderHelper->getFirstSimpleProduct($order);

        if ($amendHelper->isAmendmentPage() && $orderamendOrderHelper->isOrderItemProduct($product)) {
            $amendedOrder = $amendHelper->getOrder();

            if ($order->getCustomerGroupId() !== $amendedOrder->getCustomerGroupId()) {
                $allAppliedRules = Mage::getModel('rockar_orderamend/catalogRule_data')->load(
                    $amendedOrder->getId(),
                    'order_id'
                )->getData('all_catalog_rule_prices');
                $allAppliedRules = $allHelper->jsonDecode($allAppliedRules);

                if ($allAppliedRules && array_column($allAppliedRules, 'applied_rules')) {
                    $currentCustGroupRules = array_column($allAppliedRules, 'customer_group_id');

                    $productRulesDataIndex = array_search(
                        $order->getCustomerGroupId(),
                        $currentCustGroupRules
                    );

                    if ($productRulesDataIndex) {
                        $productRulesData = $allHelper->jsonEncode($allAppliedRules[$productRulesDataIndex]['applied_rules']);
                    } else {
                        $productRulesData = 'No rules applied';
                    }
                }
            }

            $productRulesData = $productRulesData ?? $amendedOrder->getPricingRuleSnapshot();
        } else {
            $ruleIds = $catalogueRuleHelper->getCurrentAppliedRuleIds($product);

            $rules = Mage::getModel('catalogrule/rule')->getCollection()
                ->addFieldToFilter('rule_id', ['in' => $ruleIds]);

            foreach ($rules as $rule) {
                $productRulesData[] = [
                    'rule_id' => $rule->getId(),
                    'rule_name' => $rule->getName(),
                    'rule_apply' => $rule->getSimpleAction(),
                    'rule_discount_amount' => $rule->getDiscountAmount(),
                    'rule_priority' => $rule->getSortOrder()
                ];
            }

            if ($productRulesData) {
                $productRulesData = $allHelper->jsonEncode($productRulesData);
            }
        }

        $order->setData('pricing_rule_snapshot', $productRulesData ?: 'No rules applied')
            ->save();
    }

    /**
     * @inheritDoc
     */
    public function dailyCatalogUpdate($observer)
    {
        /** @var $model Mage_CatalogRule_Model_Rule */
        $model = Mage::getSingleton('catalogrule/rule');
        $model->applyAll();
        Mage::dispatchEvent('peppermint_catalogrules_daily_catalog_update_after');

        return $this;
    }
}

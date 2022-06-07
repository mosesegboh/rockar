<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Orderamend
 * @author    Jiraphong Witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Orderamend_Model_CatalogRule_Observer extends Rockar_Orderamend_Model_CatalogRule_Observer
{
    /**
     * Save CatalogRule related data to separate table for order amend
     * Rewrite of core function to check for simple product if exists
     *
     * @param Varien_Event_Observer $observer
     * @return boolean
     * @throws Mage_Core_Model_Store_Exception
     */
    public function saveOrderCatalogRuleData(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $helper = Mage::helper('rockar_checkout/order');
        $product = $helper->getFirstVisibleItem($order)
            ->getProduct();
        $simpleOrderItem = $helper->getFirstSimpleOrderItem($order);

        // if simple orderItem exist use that since the we are checking out with simple not configurable product
        if ($simpleOrderItem) {
            $product = $simpleOrderItem->getProduct();
        }

        $date = Mage::getModel('core/date')->date();

        /** @var Rockar_Orderamend_Helper_Data $amendHelper */
        $amendHelper = Mage::helper('rockar_orderamend');

        if ($amendHelper->isAmendmentPage() && Mage::helper('rockar_orderamend/order')->isOrderItemProduct($product)) {
            /** @var Mage_CatalogRule_Model_Rule_Product_Price $couponModel */
            $originalOrder = $amendHelper->getOrder();
            $catalogRuleData = Mage::getModel('rockar_orderamend/catalogRule_data')->load(
                $originalOrder->getId(),
                'order_id'
            );
        } else {
            $catalogRuleData = $this->_getNewCouponData($order, $product);
        }

        $catalogRuleData->unsetData($catalogRuleData->getIdFieldName());

        $catalogRuleData->setData('order_id', $order->getId())
            ->setCreatedAt($date)
            ->setUpdatedAt($date)
            ->save();

        return true;
    }

    /**
     * Get Catalog Rule data to save in order
     *
     * @param $order
     * @param $product
     * @return Rockar_Orderamend_Model_CatalogRule_Data
     * @throws Mage_Core_Model_Store_Exception
     */
    protected function _getNewCouponData($order, $product)
    {
        $catalogRuleData = parent::_getNewCouponData($order, $product);

        // add all available prices per customer groups
        $todayDate = $product->getResource()->formatDate(time(), false);

        /** @var Mage_CatalogRule_Model_Rule_Product_Price $couponModel */
        $catalogRulesPricesCollection = Mage::getModel('catalogrule/rule_product_price')->getCollection()
            ->addFieldToFilter('product_id', $product->getId())
            ->addFieldToFilter('website_id', Mage::app()->getStore($order->getStoreId())->getWebsiteId())
            ->addFieldToFilter('rule_date', $todayDate);

        $prices = [];

        $ruleFields = [
            'rule_name' => 'name',
            'rule_apply' => 'simple_action',
            'rule_discount_amount' => 'discount_amount',
            'rule_priority' => 'sort_order',
        ];

        $catalogRuleProductCollection = Mage::getModel('peppermint_catalogrule/rule_product')->getCollection()
            ->addFieldToFilter('product_id', $product->getId())
            ->addFieldToFilter('website_id', Mage::app()->getStore($order->getStoreId())->getWebsiteId());

        $catalogRuleProductCollection
            ->getSelect()
            ->joinInner(
                ['rule' => Mage::getSingleton('core/resource')->getTableName('catalogrule/rule')],
                'main_table.rule_id = rule.rule_id',
                $ruleFields
            );

        foreach ($catalogRulesPricesCollection as $item) {
            $customerGroupId = $item->getCustomerGroupId();
            $applicableRules = [];

            foreach ($catalogRuleProductCollection as $rule) {
                if ($customerGroupId == $rule->getData('customer_group_id')) {
                    $applicableRules[] = array_intersect_key(
                        $rule->getData(),
                        ['rule_id' => 'rule_id'] + $ruleFields
                    );
                }
            }

            $prices[] = [
                'customer_group_id' => $customerGroupId,
                'rule_price' => $item->getRulePrice(),
                'applied_rules' => $applicableRules
            ];
        }

        if ($prices) {
            $catalogRuleData = $catalogRuleData->addData(
                ['all_catalog_rule_prices' => Mage::helper('rockar_all')->jsonEncode($prices)]
            );
        }

        return $catalogRuleData;
    }
}

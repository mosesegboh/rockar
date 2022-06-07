<?php
/**
 * @category  Peppermint
 * @package   Peppermint_SalesRule
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_SalesRule_Model_Resource_RulePending_Collection extends Mage_SalesRule_Model_Resource_Rule_Collection
{
    /**
     * Store associated with rule entities information map
     *
     * @var array
     */
    protected $_associatedEntitiesMap = [];

    /**
     * Set resource model and determine field mapping
     */
    protected function _construct()
    {
        $this->_init('peppermint_salesrule/rulePending');
        $this->_map['fields']['rule_id'] = 'main_table.rule_id';
    }

    /**
     * Filter collection by specified website, customer group, coupon code, date.
     * Filter collection to use only active rules.
     * Involved sorting by sort_order column.
     *
     * @param int         $websiteId
     * @param int         $customerGroupId
     * @param string      $couponCode
     * @param string|null $now
     * @return $this
     */
    public function setValidationFilter($websiteId, $customerGroupId, $couponCode = '', $now = null)
    {
        // pending model, dont validate
        return $this;
    }

    /**
     * Filter collection by website(s), customer group(s) and date.
     * Filter collection to only active rules.
     * Sorting is not involved
     *
     * @param int         $websiteId
     * @param int         $customerGroupId
     * @param string|null $now
     * @return $this
     */
    public function addWebsiteGroupDateFilter($websiteId, $customerGroupId, $now = null)
    {
        return $this;
    }

    /**
     * Limit rules collection by specific websites
     *
     * @param int|array|Mage_Core_Model_Website $websiteId
     * @return Mage_Rule_Model_Resource_Rule_Collection_Abstract
     */
    public function addWebsiteFilter($websiteId)
    {
        $websiteId = (int) $websiteId;

        $this->getSelect()->where(
            'FIND_IN_SET(' . (int) $websiteId . ', website_ids)'
        );

        return $this;
    }

    /**
     * Limit rules collection by specific customerGroups
     *
     * @param int|array|Mage_Core_Model_Website $customerGroupId
     * @return Mage_Rule_Model_Resource_Rule_Collection_Abstract
     */
    public function addCustomerGroupFilter($customerGroupId)
    {
        $this->getSelect()->where(
            'FIND_IN_SET(' . (int) $customerGroupId . ', customer_group_ids)'
        );

        return $this;
    }

    /**
     * Add primary coupon to collection
     *
     * @return Mage_SalesRule_Model_Resource_Rule_Collection
     */
    public function _initSelect()
    {
        $this->getSelect()
            ->from(['main_table' => $this->getMainTable()])
            ->joinLeft(
                ['rule_coupons' => $this->getTable('peppermint_salesrule/coupon_pending')],
                'main_table.rule_id = rule_coupons.rule_id AND rule_coupons.is_primary = 1',
                ['code']
            );

        return $this;
    }
}

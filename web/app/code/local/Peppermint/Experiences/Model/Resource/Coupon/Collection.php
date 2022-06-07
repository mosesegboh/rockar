<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Experiences
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Experiences_Model_Resource_Coupon_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('peppermint_experiences/coupon');
    }

    /**
     * Add rule to filter
     *
     * @param Peppermint_Experiences_Model_ExperiencesRules|int $rule
     *
     * @return Peppermint_Experiences_Model_Resource_Coupon_Collection
     */
    public function addRuleToFilter($rule)
    {
        if ($rule instanceof Peppermint_Experiences_Model_ExperiencesRules) {
            $ruleId = $rule->getId();
        } else {
            $ruleId = (int) $rule;
        }

        $this->addFieldToFilter('rule_id', $ruleId);

        return $this;
    }

    /**
     * Add rule IDs to filter
     *
     * @param array $ruleIds
     *
     * @return Peppermint_Experiences_Model_Resource_Coupon_Collection
     */
    public function addRuleIdsToFilter(array $ruleIds)
    {
        $this->addFieldToFilter('rule_id', ['in' => $ruleIds]);

        return $this;
    }

    /**
     * Filter collection to be filled with auto-generated coupons only
     *
     * @return Peppermint_Experiences_Model_Resource_Coupon_Collection
     */
    public function addGeneratedCouponsFilter()
    {
        $this->addFieldToFilter('is_primary', 0)->addFieldToFilter('type', '1');

        return $this;
    }

    /**
     * Callback function that filters collection by field "Used" from grid
     *
     * @param Mage_Core_Model_Resource_Db_Collection_Abstract $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     */
    public function addIsUsedFilterCallback($collection, $column)
    {
        $filterValue = $column->getFilter()->getCondition();

        $fieldExpression = $this->getConnection()->getCheckSql('main_table.times_used > 0', 1, 0);
        $resultCondition = $this->_getConditionSql($fieldExpression, ['eq' => $filterValue]);
        $collection->getSelect()->where($resultCondition);
    }
}

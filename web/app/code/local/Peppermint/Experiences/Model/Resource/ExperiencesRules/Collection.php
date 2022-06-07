<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Experiences
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Experiences_Model_Resource_ExperiencesRules_Collection extends Mage_Rule_Model_Resource_Rule_Collection_Abstract
{
    /**
     * Store associated with rule entities information map
     *
     * @var array
     */
    protected $_associatedEntitiesMap = [
        'website' => [
            'associations_table' => 'peppermint_experiences/experiences_rules_website',
            'rule_id_field' => 'rule_id',
            'entity_id_field' => 'website_id'
        ],
        'customer_group' => [
            'associations_table' => 'peppermint_experiences/experiences_rules_customer_group',
            'rule_id_field' => 'rule_id',
            'entity_id_field' => 'customer_group_id'
        ]
    ];

    /**
     * Initialize resource model
     */
    public function _construct()
    {
        $this->_init('peppermint_experiences/experiencesRules');
        $this->_map['fields']['rule_id'] = 'main_table.rule_id';
    }

    /**
     * Filter collection by specified website, customer group, coupon code, date.
     * Filter collection to use only active rules.
     *
     * @param int $websiteId
     * @param int $customerGroupId
     * @param string $couponCode
     * @param string|null $now
     * @use $this->addWebsiteGroupDateFilter()
     *
     * @return $this
     */
    public function setValidationFilter($websiteId, $customerGroupId, $couponCode = '', $now = null)
    {
        if (!$this->getFlag('validation_filter')) {
            /* We need to overwrite joinLeft if coupon is applied */
            $this->getSelect()->reset();
            parent::_initSelect();

            $this->addWebsiteGroupDateFilter($websiteId, $customerGroupId, $now);
            $select = $this->getSelect();

            $connection = $this->getConnection();

            if (strlen($couponCode)) {
                $select->joinLeft(
                    ['rule_coupons' => $this->getTable('peppermint_experiences/coupon')],
                    $connection->quoteInto(
                        'main_table.rule_id = rule_coupons.rule_id AND main_table.coupon_type != ?',
                        Peppermint_Experiences_Model_ExperiencesRules::COUPON_TYPE_NO_COUPON
                    ),
                    ['code']
                );

                $noCouponCondition = $connection->quoteInto(
                    'main_table.coupon_type = ? ',
                    Peppermint_Experiences_Model_ExperiencesRules::COUPON_TYPE_NO_COUPON
                );

                $orWhereConditions = [
                    $connection->quoteInto(
                        '(main_table.coupon_type = ? AND main_table.use_auto_generation = 1 AND rule_coupons.type = 1)',
                        Peppermint_Experiences_Model_ExperiencesRules::COUPON_TYPE_SPECIFIC
                    ),
                    $connection->quoteInto(
                        '(main_table.coupon_type = ? AND main_table.use_auto_generation = 0 AND rule_coupons.type = 0)',
                        Peppermint_Experiences_Model_ExperiencesRules::COUPON_TYPE_SPECIFIC
                    ),
                ];
                $orWhereCondition = implode(' OR ', $orWhereConditions);
                $select->where(
                    $noCouponCondition . ' OR ((' . $orWhereCondition . ') AND rule_coupons.code = ?)', $couponCode
                );
            } else {
                $this->addFieldToFilter('main_table.coupon_type', Peppermint_Experiences_Model_ExperiencesRules::COUPON_TYPE_NO_COUPON);
            }

            $this->setFlag('validation_filter', true);
        }

        return $this;
    }

    /**
     * Filter collection by website(s), customer group(s) and date.
     * Filter collection to only active rules.
     * Sorting is not involved
     *
     * @param int $websiteId
     * @param int $customerGroupId
     * @param string|null $now
     * @use $this->addWebsiteFilter()
     *
     * @return $this
     */
    public function addWebsiteGroupDateFilter($websiteId, $customerGroupId, $now = null)
    {
        if (!$this->getFlag('website_group_date_filter')) {
            if (is_null($now)) {
                $now = Mage::getModel('core/date')->date('Y-m-d');
            }

            $this->addWebsiteFilter($websiteId);

            $entityInfo = $this->_getAssociatedEntityInfo('customer_group');
            $connection = $this->getConnection();
            $this->getSelect()
                ->joinInner(
                    ['customer_group_ids' => $this->getTable($entityInfo['associations_table'])],
                    $connection->quoteInto(
                        'main_table.' . $entityInfo['rule_id_field']
                        . ' = customer_group_ids.' . $entityInfo['rule_id_field']
                        . ' AND customer_group_ids.' . $entityInfo['entity_id_field'] . ' = ?',
                        (int) $customerGroupId
                    ),
                    []
                )
                ->joinInner(
                    ['experiences' => $this->getTable('peppermint_experiences/experiences')],
                    'main_table.experience_id = experiences.experience_id',
                    []
                )
                ->where('from_date is null or from_date <= ?', $now)
                ->where('to_date is null or to_date >= ?', $now);

            $this->addIsActiveFilter()
                ->setOrder('priority', self::SORT_ORDER_ASC)
                ->addOrder('rule_id', self::SORT_ORDER_ASC)
                ->setFlag('validation_filter', true)
                ->setFlag('website_group_date_filter', true);
        }

        return $this;
    }

    /**
     * Add primary coupon to collection
     *
     * @return $this
     */
    public function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()
            ->joinLeft(
                ['rule_coupons' => $this->getTable('peppermint_experiences/coupon')],
                'main_table.rule_id = rule_coupons.rule_id AND rule_coupons.is_primary = 1',
                ['code']
            );

        return $this;
    }

    /**
     * Find product attribute in conditions or actions
     *
     * @param string $attributeCode
     *
     * @return $this
     */
    public function addAttributeInConditionFilter($attributeCode)
    {
        $match = sprintf('%%%s%%', substr(serialize(['attribute' => $attributeCode]), 5, -1));
        $field = $this->_getMappedField('conditions_serialized');
        $cCond = $this->_getConditionSql($field, ['like' => $match]);
        $field = $this->_getMappedField('actions_serialized');
        $aCond = $this->_getConditionSql($field, ['like' => $match]);

        $this->getSelect()->where(sprintf('(%s OR %s)', $cCond, $aCond), null, Varien_Db_Select::TYPE_CONDITION);

        return $this;
    }

    /**
     * Excludes price rules with generated specific coupon codes from collection
     *
     * @return $this
     */
    public function addAllowedRulesFilter()
    {
        $this->addFieldToFilter(
            'main_table.use_auto_generation',
            ['neq' => 1]
        );

        return $this;
    }
}

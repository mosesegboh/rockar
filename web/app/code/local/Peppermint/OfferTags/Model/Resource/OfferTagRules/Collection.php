<?php
/**
 * @category  Peppermint
 * @package   Peppermint_OfferTags
 * @author    Ketevani Revazishvili<techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_OfferTags_Model_Resource_OfferTagRules_Collection extends Mage_Rule_Model_Resource_Rule_Collection_Abstract
{
    /**
     * Store associated with rule entities information map
     *
     * @var array
     */
    protected $_associatedEntitiesMap = [
        'website' => [
            'associations_table' => 'peppermint_offertags/offertag_rules_website',
            'rule_id_field' => 'rule_id',
            'entity_id_field' => 'website_id'
        ],
        'customer_group' => [
            'associations_table' => 'peppermint_offertags/offertag_rules_customer_group',
            'rule_id_field' => 'rule_id',
            'entity_id_field' => 'customer_group_id'
        ],
        'financingoptions_group' => [
            'associations_table' => 'peppermint_offertags/offertag_rules_finance_group',
            'rule_id_field' => 'rule_id',
            'entity_id_field' => 'group_id'
        ]
    ];

    /**
     * Initialize resource model
     */
    public function _construct()
    {
        $this->_init('peppermint_offertags/offerTagRules');
        $this->_map['fields']['rule_id'] = 'main_table.rule_id';
    }

    /**
     * Filter collection by specified website, customer group date.
     * Filter collection to use only active rules.
     * Filter collection by existence of offer tags
     * Involved sorting by priority and rule_id column.
     *
     * @param int $websiteId
     * @param int $customerGroupId
     * @param string|null $now
     * @return Peppermint_OfferTags_Model_Resource_OfferTagRules_Collection
     * @throws Mage_Core_Exception
     * @use $this->addWebsiteGroupDateFilter()
     *
     */
    public function setValidationFilter($websiteId, $customerGroupId, $now = null)
    {
        if (!$this->getFlag('validation_filter')) {
            $this->getSelect()->reset();
            parent::_initSelect();

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
                    ['offer_tags' => $this->getTable('peppermint_offertags/offertags')],
                    'main_table.offer_tag_id = offer_tags.offertag_id',
                    []
                )
                ->where('from_date is null or from_date <= ?', $now)
                ->where('to_date is null or to_date >= ?', $now);

            $this->addIsActiveFilter()
                ->setOrder('priority', self::SORT_ORDER_ASC)
                ->addOrder('rule_id', self::SORT_ORDER_ASC)
                ->setFlag('validation_filter', true);
        }

        return $this;
    }
}

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_OfferTags
 * @author    Ketevani Revazishvili<techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_OfferTags_Model_Resource_OfferTagRules extends Mage_Rule_Model_Resource_Abstract
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
     * Initialize connection and define main table
     */
    protected function _construct()
    {
        $this->_init('peppermint_offertags/offertag_rules', 'rule_id');
    }

    /**
     * Add customer group ids and website ids to rule data after load
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $object->setData('customer_group_ids', (array)$this->getCustomerGroupIds($object->getId()));
        $object->setData('website_ids', (array)$this->getWebsiteIds($object->getId()));
        $object->setData('finance_group_ids', (array)$this->getFinanceGroupIds($object->getId()));

        return parent::_afterLoad($object);
    }

    /**
     * Bind offer tag rule to customer group(s) and website(s).
     * Update products which are matched for rule.
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return Mage_Core_Model_Resource_Db_Abstract
     * @throws Exception
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        if ($object->hasWebsiteIds()) {
            $websiteIds = $object->getWebsiteIds();

            if (!is_array($websiteIds)) {
                $websiteIds = explode(',', (string)$websiteIds);
            }

            $this->bindRuleToEntity($object->getId(), $websiteIds, 'website');
        }

        if ($object->hasCustomerGroupIds()) {
            $customerGroupIds = $object->getCustomerGroupIds();

            if (!is_array($customerGroupIds)) {
                $customerGroupIds = explode(',', (string)$customerGroupIds);
            }

            $this->bindRuleToEntity($object->getId(), $customerGroupIds, 'customer_group');
        }

        if ($object->hasFinanceGroupIds()) {
            $financeGroupIds = $object->getFinanceGroupIds();

            if (!is_array($financeGroupIds)) {
                $financeGroupIds = explode(',', (string)$financeGroupIds);
            }

            $this->bindRuleToEntity($object->getId(), $financeGroupIds, 'financingoptions_group');
        }

        return parent::_afterSave($object);
    }

    /**
     * Get offer tag rule finance group Ids
     *
     * @return array
     */
    public function getFinanceGroupIds($ruleId)
    {
        return $this->getAssociatedEntityIds($ruleId, 'financingoptions_group');
    }
}

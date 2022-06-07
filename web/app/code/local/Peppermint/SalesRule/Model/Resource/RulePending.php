<?php
/**
 * @category  Peppermint
 * @package   Peppermint_SalesRule
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar Ltd (http://rockar.com)
 */

class Peppermint_SalesRule_Model_Resource_RulePending extends Mage_SalesRule_Model_Resource_Rule
{
    /**
     * Store associated with rule entities information map
     *
     * @var array
     */
    protected $_associatedEntitiesMap = [];

    /**
     * Initialize main table and table id field
     */
    protected function _construct()
    {
        $this->_init('peppermint_salesrule/rule_pending', 'rule_id');
    }

    /**
     * Add customer group ids and website ids to rule data after load
     *
     * @param Mage_Core_Model_Abstract $object
     * @return $this
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $object->setData('website_ids', explode(',', $object->getData('website_ids')));
        $object->setData('customer_group_ids', explode(',', $object->getData('customer_group_ids')));

        return $this;
    }

    /**
     * Bind sales rule to customer group(s) and website(s).
     * Save rule's associated store labels.
     * Save product attributes used in rule.
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_SalesRule_Model_Resource_Rule
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        return $this;
    }
}

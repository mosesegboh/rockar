<?php
/**
 * @category  Peppermint
 * @package   Peppermint_PartExchange
 * @author    Ausma Smite <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_PartExchange_Model_PromotionsPending extends Mage_Rule_Model_Abstract
{
    /**
     * Pending actions types
     */
    const ACTION_CREATE = 'Create';
    const ACTION_UPDATE = 'Update';
    const ACTION_DELETE = 'Delete';

    /**
     * Getter for rule combine conditions instance
     *
     * @return Mage_Rule_Model_Condition_Combine
     */
    public function getConditionsInstance()
    {
        return Mage::getModel('rockar_partexchange/promotions_rule_condition_combine');
    }

    /**
     * Getter for rule actions collection instance
     *
     * @return Rockar_PartExchange_Model_Promotions_Rule_Action_Combine
     */
    public function getActionsInstance()
    {
        return Mage::getModel('rockar_partexchange/promotions_rule_action_combine');
    }

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('peppermint_partexchange/promotionsPending');
    }

    /**
     * Prepare data before saving
     *
     * @return Mage_Rule_Model_Abstract
     */
    protected function _beforeSave()
    {
        // Check if discount amount not negative
        if ($this->hasDiscountAmount()) {
            if ((int)$this->getDiscountAmount() < 0) {
                Mage::throwException(Mage::helper('rule')->__('Invalid discount amount.'));
            }
        }

        // Serialize conditions
        if ($this->getConditions()) {
            $this->setConditionsSerialized(serialize($this->getConditions()->asArray()));
            $this->unsConditions();
        }

        // Serialize actions
        if ($this->getActions()) {
            $this->setActionsSerialized(serialize($this->getActions()->asArray()));
            $this->unsActions();
        }

        if ($this->hasWebsiteIds()) {
            $websiteIds = $this->getWebsiteIds();
            if (is_array($websiteIds) && !empty($websiteIds)) {
                $this->setWebsiteIds(implode(',', $websiteIds));
            }
        }

        if ($this->hasCustomerGroupIds()) {
            $groupIds = $this->getCustomerGroupIds();
            if (is_array($groupIds) && !empty($groupIds)) {
                $this->setCustomerGroupIds(implode(',', $groupIds));
            }
        }

        Mage_Core_Model_Abstract::_beforeSave();

        return $this;
    }
}

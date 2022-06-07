<?php
/**
 * @category  Peppermint
 * @package   Peppermint_OfferTags
 * @author    Ketevani Revazishvili<techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_OfferTags_Model_OfferTagRules extends Mage_Rule_Model_Abstract
{
    protected $_eventPrefix = 'peppermint_offertag_rules';

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('peppermint_offertags/offerTagRules');
    }

    /**
     * @return Mage_Core_Controller_Request_Http
     */
    protected function _getRequest()
    {
        return Mage::app()->getRequest();
    }

    /**
     * Get offer tag rule finance group Ids
     *
     * @return array
     */
    public function getFinanceGroupIds()
    {
        if (!$this->hasFinanceGroupIds()) {
            $financeGroupIds = $this->_getResource()->getFinanceGroupIds($this->getId());
            $this->setData('finance_group_ids', (array) $financeGroupIds);
        }
        
        return $this->_getData('finance_group_ids');
    }

    /**
     * Get rule condition combine model instance
     *
     * @return Peppermint_OfferTags_Model_Condition_Combine
     */
    public function getConditionsInstance()
    {
        return Mage::getModel('peppermint_offertags/condition_combine');
    }

    /**
     * Get rule condition combine model instance
     *
     * @return Peppermint_OfferTags_Model_Condition_Combine
     */
    public function getActionsInstance()
    {
        return Mage::getModel('peppermint_offertags/condition_combine');
    }
}

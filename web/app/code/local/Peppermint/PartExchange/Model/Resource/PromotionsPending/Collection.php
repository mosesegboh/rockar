<?php
/**
 * @category  Peppermint
 * @package   Peppermint_PartExchange
 * @author    Ausma Smite <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_PartExchange_Model_Resource_PromotionsPending_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('peppermint_partexchange/promotionsPending');
    }

    /**
     * Redeclare after load method for specifying collection items original data
     *
     * @return Peppermint_PartExchange_Model_Resource_PromotionsPending_Collection|void
     */
    protected function _afterLoad()
    {
        foreach ($this->_items as $item) {
            $item->setData('website_ids', explode(',', $item->getWebsiteIds()));
            $item->setData('customer_group_ids', explode(',', $item->getCustomerGroupIds()));
        }

        parent::_afterLoad();
    }
}

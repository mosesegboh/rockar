<?php
/**
 * @category  Peppermint
 * @package   Peppermint_PartExchange
 * @author    Ausma Smite <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_PartExchange_Model_Resource_PromotionsPending extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize connection and define main table
     *
     */
    protected function _construct()
    {
        $this->_init('peppermint_partexchange/promotions_pending', 'rule_id');
    }
}

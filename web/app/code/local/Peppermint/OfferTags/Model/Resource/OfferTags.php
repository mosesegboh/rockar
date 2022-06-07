<?php
/**
 * @category  Peppermint
 * @package   Peppermint_OfferTags
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_OfferTags_Model_Resource_OfferTags extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize connection and define main table
     */
    protected function _construct()
    {
        $this->_init('peppermint_offertags/offertags', 'offertag_id');
    }
}

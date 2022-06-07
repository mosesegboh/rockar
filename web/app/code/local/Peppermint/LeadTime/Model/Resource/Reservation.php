<?php
/**
 * @category  Peppermint
 * @package   Peppermint_LeadTime
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_LeadTime_Model_Resource_Reservation extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Magento post __construct init.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('peppermint_leadtime/reservation', 'id');
    }
}

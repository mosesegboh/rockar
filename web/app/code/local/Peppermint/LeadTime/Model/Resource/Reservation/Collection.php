<?php
/**
 * @category     Peppermint
 * @package      Peppermint_LeadTime
 * @author       Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright    Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_LeadTime_Model_Resource_Reservation_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('peppermint_leadtime/reservation');
    }
}

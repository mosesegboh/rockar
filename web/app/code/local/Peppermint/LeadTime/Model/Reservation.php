<?php
/**
 * @category  Peppermint
 * @package   Peppermint_LeadTime
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_LeadTime_Model_Reservation extends Mage_Core_Model_Abstract
{
    /**
     * Prefix of model events
     *
     * @var string
     */
    protected $_eventPrefix = 'peppermint_leadtime_reservation';

    /**
     * Magento post __construct init.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('peppermint_leadtime/reservation');
    }
}

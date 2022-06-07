<?php
/**
 * @category  Peppermint
 * @package   Peppermint_PartExchange
 * @author    Ausma Smite <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar Ltd (http://rockar.com)
 */

class Peppermint_PartExchange_Block_Filters extends Rockar2_PartExchange_Block_Filters
{
    /**
     * Returns a Temporary Part Exchange VRM and Mileage from Session
     *
     * @return string
     */
    public function getTemporaryPartExchangeJson()
    {
        return Mage::helper('peppermint_partexchange')->getTemporaryPartExchangeJson();
    }
}

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FutureValue
 * @author    Ausma Smite <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_FutureValue_Helper_Data extends Rockar_FutureValue_Helper_Data
{
    /**
     * Get updated value for px info block
     * Rewrites formatting
     *
     * @param $px
     * @param $leadTime
     * @return mixed
     */
    protected function _getLeadTimeValue($px, $leadTime)
    {
        return str_replace(',', ' ', preg_replace('/\.[0-9]+/', '',
            Mage::helper('core')->currency(Mage::helper('rockar_futurevalue/deductions_partExchangeFuture_data')
                ->deduct($px, '', $leadTime), true, false)));
    }
}

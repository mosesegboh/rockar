<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Dfe
 * @author    Lucian Mesaros <lucian.mesaros@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Dfe_Helper_Application_PreviousAddress extends Mage_Core_Helper_Abstract
{
    /**
     * Set data for previous address.
     *
     * @param  integer $orderId
     * @return object
     */
    public function setPreviousAddressData($orderId)
    {
        $data = $this->_prepareData($orderId);

        return (new Peppermint_Dfe_Soap_Application_Address())->setTimeAtAddressInYears($data['duration_year'])
            ->setTimeAtAddressInMonths($data['duration_month']);
    }

    /**
     * Prepare collection data by orderID.
     *
     * @param  integer $orderId
     * @return []
     */
    protected function _prepareData($orderId)
    {
        $durationTime = Mage::getModel('rockar_checkout/order_additional_residence')->load($orderId, 'order_id')
            ->getData('duration_at_previous_address') ?? 0;

        if ((int) ($durationTime) > 0) {
            $durationYearMonth = Mage::helper('peppermint_dfe')->transformToYearMonth($durationTime);
            $durationYear = $durationYearMonth['years'];
            $durationMonth = $durationYearMonth['months'];
        } else {
            $durationYear = 0;
            $durationMonth = 0;
        }

        return [
            'duration_year' => $durationYear,
            'duration_month' => $durationMonth
        ];
    }
}

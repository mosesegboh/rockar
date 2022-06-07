<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Dfe
 * @author    Lucian Mesaros <lucian.mesaros@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Dfe_Helper_Application_ResidentialAddress extends Mage_Core_Helper_Abstract
{
    /**
     * Set data for residential address.
     *
     * @param  $orderId
     * @return object
     */
    public function setResidentialAddressData($orderId)
    {
        $data = $this->_prepareData($orderId);

        return (new Peppermint_Dfe_Soap_Application_Address())->setAddressLine1($data['address_1'])
            ->setAddressLine2($data['address_2'])
            ->setAddressLine3('')
            ->setSuburb($data['region'])
            ->setPostalCode($data['postcode'])
            ->setTimeAtAddressInYears($data['duration_year_current_address'])
            ->setTimeAtAddressInMonths($data['duration_month_current_address']);
    }

    /**
     * Prepare collection data by orderID.
     *
     * @param  integer $orderId
     * @return []
     */
    protected function _prepareData($orderId)
    {
        $dataAddress = Mage::getModel('sales/order_address')->getCollection()
            ->addFieldToFilter('parent_id', $orderId)
            ->addFieldToFilter('address_type', 'billing')
            ->addFieldToSelect(['region', 'postcode', 'street'])
            ->setCurPage(1)
            ->setPageSize(1)
            ->getFirstItem()
            ->getData();
        $streetAddress = explode(PHP_EOL, $dataAddress['street']);

        $durationAtCurrentAddress = Mage::getModel('rockar_checkout/order_additional_residence')->load($orderId, 'order_id')
            ->getDurationAtCurrentAddress();

        if ((int) ($durationAtCurrentAddress) > 0) {
            $dataYearCurrent = Mage::helper('peppermint_dfe')->transformToYearMonth($durationAtCurrentAddress);
            $durationYearCurrentAddress = $dataYearCurrent['years'];
            $durationMonthCurrentAddress = $dataYearCurrent['months'];
        } else {
            $durationYearCurrentAddress = 0;
            $durationMonthCurrentAddress = 0;
        }

        return [
            'address_1' => $streetAddress[0] ?? '',
            'address_2' => $streetAddress[1] ?? '',
            'region' => $dataAddress['region'] ?? '',
            'postcode' => $dataAddress['postcode'] ?? '',
            'duration_year_current_address' => $durationYearCurrentAddress,
            'duration_month_current_address' => $durationMonthCurrentAddress
        ];
    }
}

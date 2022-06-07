<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Dfe
 * @author    Lucian Mesaros <lucian.mesaros@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Dfe_Helper_Application_ResidentialDetails extends Mage_Core_Helper_Abstract
{
    /**
     * Set data for residential details.
     *
     * @param  integer $orderId
     * @return object
     */
    public function setResidentialDetailsData($orderId)
    {
        $data = $this->_prepareData($orderId);

        return (new Peppermint_Dfe_Soap_Application_ResidentialInformationDetails())->setTypeOfHouse('')
            ->setResidentialStatusCode($data['ownership'])
            ->setResidentialOwner($data['accommodation_type'])
            ->setHomeStatusCode($data['house_status'])
            ->setBalanceOwing(0)
            ->setAccessFacilityFlag('')
            ->setFaciltyValue(0);
    }

    /**
     * Prepare collection data by orderID.
     *
     * @param  integer $orderId
     * @return []
     */
    protected function _prepareData($orderId)
    {
        $data = Mage::getModel('rockar_checkout/order_additional_residence')->load($orderId, 'order_id')
            ->getData();

        return [
            'ownership' => $data['ownership'] ?? '',
            'accommodation_type' => $data['accommodation_type'] ?? '',
            'house_status' => $data['house_status'] ?? ''
        ];
    }
}

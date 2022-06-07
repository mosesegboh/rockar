<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Dfe
 * @author    Lucian Mesaros <lucian.mesaros@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Dfe_Helper_Application_PostalAddress extends Mage_Core_Helper_Abstract
{
    /**
     * Set data for postal address.
     *
     * @param  integer $orderId
     * @return object
     */
    public function setPostalAddressData($orderId)
    {
        $data = $this->_prepareData($orderId);

        return (new Peppermint_Dfe_Soap_Application_Address())->setAddressLine1($data['address_1'])
            ->setAddressLine2($data['address_2'])
            ->setAddressLine3('')
            ->setSuburb($data['region'])
            ->setPostalCode($data['postcode']);
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
            'address_1' => $data['address_1'] ?? '',
            'address_2' => $data['address_2'] ?? '',
            'region' => $data['region'] ?? '',
            'postcode' => $data['postcode'] ?? ''
        ];
    }
}

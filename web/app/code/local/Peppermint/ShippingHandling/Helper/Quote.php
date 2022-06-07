<?php

/**
 * @category  Peppermint
 * @package   Peppermint_ShippingHandling
 * @author    Sergejs Plisko <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */
class Peppermint_ShippingHandling_Helper_Quote extends Rockar_ShippingHandling_Helper_Quote
{
    /**
     * Format delivery date data for saving on quote
     *
     * @param $data
     * @param $quote
     *
     * @return array
     * @throws Mage_Core_Exception
     */
    public function formatDeliveryDateForQuote($data, $quote)
    {
        $queryArray = [];

        if (!$this->_validateDeliveryData($data)) {
            return $queryArray;
        }

        $localStore = Mage::getResourceModel('rockar_localstores/stores_collection')
            ->addFieldToFilter('main_table.entity_id', $data['store_select'])
            ->setCurPage(1)
            ->setPageSize(1)
            ->getFirstItem();

        if (!$localStore->getId()) {
            Mage::throwException($this->__('Local store not found for delivery details'));
        }

        $queryArray['dealer_code'] = $localStore->getData('code');
        $queryArray['dealer_name'] = $localStore->getData('name');

        switch ($data['method_select']) {
            case('home_delivery'):
                // Enabled store emulation to calculate correct matrix rates during order amendment
                $appEmulation = Mage::getSingleton('core/app_emulation');
                $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($data['store_id']);
                $rates = Mage::getModel('shipping/shipping')->collectRatesByAddress($quote->getShippingAddress());
                $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

                $matrixRates = $rates->getResult()->getRatesByCarrier('matrixrate');

                if (!$matrixRates) {
                    Mage::throwException($this->__('Home Delivery can not be calculated'));
                }

                $rate = end($matrixRates);
                $queryArray['shipping_description'] = 'Select Shipping Method - Home Delivery';
                $queryArray['shipping_method'] = 'matrixrate_' . $rate->getData('method');
                $queryArray['delivery_date'] = $data['date_select'];
                $queryArray['delivery_store_id'] = $data['store_select'];

                // Erase collection details
                $queryArray['collection_date'] = '';
                $queryArray['collection_store_id'] = '';
                break;
            case('collection_free'):
                $queryArray['shipping_description'] = 'Flat Rate - Fixed';
                $queryArray['shipping_method'] = 'flatrate_flatrate';
                $queryArray['collection_date'] = $data['date_select'];
                $queryArray['collection_store_id'] = $data['store_select'];

                // Erase delivery details
                $queryArray['delivery_date'] = '';
                $queryArray['delivery_store_id'] = '';
                break;
        }

        return $queryArray;
    }
}

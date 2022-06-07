<?php
/**
 * @category  Peppermint
 * @package   Peppermint_ShippingHandling
 * @author    Aleksejs Oboruns <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */
class Peppermint_ShippingHandling_Helper_Data extends Rockar_ShippingHandling_Helper_Data
{
    /**
     * * Output all available local stores with slots added
     *
     * @param string|int|null $leadTime
     * @param string|int|null $storeId
     *
     * @return array
     */
    public function getAvailableStoresForShippingWithSlots($leadTime = null, $storeId = null)
    {
        $productClass = Mage::helper('peppermint_localstores')->getAvailableProductClass($storeId);

        $stores = Mage::getModel('rockar_localstores/stores')->getCollection()
            ->addFieldToFilter('store_view', [['eq' => $storeId], ['eq' => 0]])
            ->addFieldToFilter('enable_checkout', ['eq' => 1])
            ->addFieldToFilter('status', ['eq' => 1])
            ->addFieldToFilter('associated_brand', ['like' => '%' . $productClass . '%']);

        return $this->formatAvailableStores($stores, $leadTime);
    }
}

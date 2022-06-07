<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Localstores
 * @author    Adrian Grigorita <adrian.grigorita@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Localstores_Helper_Data extends Rockar_Localstores_Helper_Data
{
    const ALL_STORE_VIEWS_ID = 0;
    /**
     * Get the distance between two localstores
     * ========================================
     *
     * Gets the distance between two localstores
     *
     * @param Rockar_Localstores_Model_Stores $storeA
     * @param Rockar_Localstores_Model_Stores $storeB
     *
     * @return Number - -1 if no distance found
     */
    public function getDistanceBetweenStores($storeA, $storeB)
    {
        return Mage::getModel('peppermint_localstores/distances')->getCollection()
            ->addFieldToFilter('from_store_id', $storeA->getId())
            ->addFieldToFilter('to_store_id', $storeB->getId())
            ->addFieldToSelect('distance')
            ->getFirstItem()
            ->getDistance() ?? -1;
    }

    /**
     * Returns a yes/no array, ready to use by adminhtml form field definition
     *
     * @return array
     */
    public function getSimpleValueArray()
    {
        return [
            [
                'value' => 1,
                'label' => $this->__('Yes')
            ],
            [
                'value' => 0,
                'label' => $this->__('No')
            ]
        ];
    }

    /**
     * Setup distances for all possible combinations of dealerships from other dealerships.
     *
     * @param Rockar_Localstores_Model_Address[] $dealers
     * @return []
     */
    public function calculateDistancesBetweenDealers($dealers)
    {
        $dealersDistanceData = [];

        foreach ($dealers as $fromDealer) {
            foreach ($dealers as $toDealer) {
                $dealersDistanceData[] = [
                    'from_store_id' => $fromDealer->getStoreId(),
                    'to_store_id' => $toDealer->getStoreId(),
                    'distance' => $this->_getDistance(
                        (float) $fromDealer->getLatitude(),
                        (float) $fromDealer->getLongitude(),
                        (float) $toDealer->getLatitude(),
                        (float) $toDealer->getLongitude()
                    )
                ];
            }
        }

        return $dealersDistanceData;
    }

    /**
     * Calculate distance (in kilometers) between two points given as (long, latt) pairs
     * based on Haversine formula (http://en.wikipedia.org/wiki/Haversine_formula).
     * Implementation inspired by JavaScript implementation from http://www.movable-type.co.uk/scripts/latlong.html.
     *
     * @param float $latitude1
     * @param float $longitude1
     * @param float $latitude2
     * @param float $longitude2
     * @return float
     */
    protected function _getDistance($latitude1, $longitude1, $latitude2, $longitude2)
    {
        // for miles, change earth_radius to 3956
        $earthRadius = 6371;
        $distanceLat = deg2rad($latitude2 - $latitude1);
        $distanceLon = deg2rad($longitude2 - $longitude1);
        $aDistance = sin($distanceLat / 2) * sin($distanceLat / 2) + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($distanceLon / 2) * sin($distanceLon / 2);
        $cDistance = 2 * asin(sqrt($aDistance));

        return $earthRadius * $cDistance;
    }

    /**
     * Returns product class from quote
     *
     * @param string|int|null $storeId
     *
     * @return string
     */
    public function getAvailableProductClass($storeId)
    {
        $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote()
            ->getAllVisibleItems()
            ?: Mage::getSingleton('checkout/session')->getQuote()
                ->getAllVisibleItems();

        $quoteItem = reset($quote);

        $product = Mage::getResourceModel('catalog/product_collection')->addAttributeToSelect('product_class')
            ->addIdFilter($quoteItem->getProductId())
            ->setStore($storeId)
            ->addStoreFilter()
            ->setPage(1, 1)
            ->getFirstItem();

        return $product->getProductClass() ?? '';
    }

    /**
     * Returns local store from store code
     * or null if store does not exist
     *
     * @param string $storeCode
     *
     * @return Rockar_Localstores_Model_Stores|null
     */
    public function getStoreFromCode($storeCode)
    {
        $store = Mage::getResourceModel('rockar_localstores/stores_collection')
            ->addFieldToFilter('status', Rockar_Localstores_Model_Resource_Stores_Collection::STATUS_ENABLED)
            ->addFieldToFilter('code', $storeCode)
            ->setPageSize(1)
            ->setCurPage(1)
            ->getFirstItem();

        return $store->getId() ? $store : null;
    }

    /**
     * Returns all local store names as an array
     *
     * @return array
     */
    public function getStoreOptions()
    {
        $options = [];
        $stores = Mage::getResourceModel('rockar_localstores/stores_collection')
            ->addFieldToSelect('name')
            ->addFieldToFilter('status', Rockar_Localstores_Model_Resource_Stores_Collection::STATUS_ENABLED);

        foreach ($stores as $store) {
            $options[$store->getName()] = $store->getName();
        }

        asort($options);

        return $options;
    }

    /**
     * Rewrite parent function to add 'associated_compound_dealer' attribute
     *
     * {@inheritDoc}
     */
    public function formatStoreData($stores, $type = null)
    {
        $formattedStores = parent::formatStoreData($stores, $type);
        $items = $stores->getItems();

        foreach ($formattedStores as $key => $store) {
            $formattedStores[$key]['associated_compound_dealer'] = $items[$store['id']]['associated_compound_dealer'];
            $formattedStores[$key]['has_previous_orders'] = $items[$store['id']]['previousOrders'] ? 1 : 0;
        }

        return $formattedStores;
    }
}

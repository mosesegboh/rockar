<?php
/**
* @category  Peppermint
* @package   Peppermint_Checkout
* @author    Sykander Gul <sykander.gul@rockar.com>
* @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
*/

class Peppermint_Checkout_Helper_ReplacementProduct extends Mage_Core_Helper_Abstract
{
    const PRODUCT_EXPIRED_CHANGED_PRICE_BLOCK = 'product_expired_changed_price';
    const PRODUCT_EXPIRED_CHANGED_LEADTIME_BLOCK = 'product_expired_changed_leadtime';
    const PRODUCT_EXPIRED_NO_REPLACE = 'product_expired_no_replace';

    /**
     * cache quote data
     *
     * @var array
     */
    protected $_cacheData = [];

    /**
     * Get next simple product for a checkout
     * ======================================
     *
     * based on BMW business rules listed below in prioritised order
     * Cheapest simple product first
     * Closest product if multiple with same cheapest price
     * null if no other simple products
     *
     * @param Mage_Catalog_Model_Product $configurableProduct
     * @param Rockar_Localstores_Model_Stores $collectionLocation
     * @param Mage_Catalog_Model_Product|null $currentProduct
     *
     * @return Mage_Catalog_Model_Product|null
     */
    public function getCheapestClosestSimpleProduct(
        Mage_Catalog_Model_Product $configurableProduct,
        Rockar_Localstores_Model_Stores $collectionLocation = null,
        $simpleProductCollection = null,
        Mage_Catalog_Model_Product $currentProduct = null,
        $allowCurrent = true
    ) {
        try {
            return $this->_getCheapestClosestSimpleProduct(
                $configurableProduct,
                $collectionLocation,
                $simpleProductCollection,
                $currentProduct,
                $allowCurrent
            );
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return null;
    }

    /**
     * Check logic if product should be replaced
     *
     * @param $parentItem
     * @param $childItem
     * @return bool
     */
    public function canProductBeReplaced($parentItem, $childItem)
    {
        $simpleProductCollection = $this->_getSimpleSiblingsCollection($parentItem, $childItem);

        // product can be replaced if there are available simple products except the current one
        return $simpleProductCollection ? $simpleProductCollection->getSize() > 0 : false;
    }

    /**
     * Replace the product in cart with a new one
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param bool $hasToReplace is replacement necessary
     *
     * @return bool
     * @throws Mage_Core_Model_Store_Exception
     */
    public function replaceProduct($parentItem, $childItem, $hasToReplace = false)
    {
        $quote = $this->_getCheckoutSessQuote();
        $simpleProductCollection = $this->_getSimpleSiblingsCollection($parentItem, $childItem);
        $localStoreId = $quote->getData('collection_store_id') ?: $quote->getData('delivery_store_id');
        $localStore = $localStoreId ? Mage::getModel('rockar_localstores/stores')->load($localStoreId) : null;

        /**
         * We can replace product only if it has parent item and siblings to choose from
         */
        if ($simpleProductCollection) {
            $configurableProduct = Mage::getModel('catalog/product')->setStoreId(Mage::app()->getStore()->getId())
                ->load($parentItem->getProductId());
            /**
             * Find the new product
             */
            $newProduct = $this->getCheapestClosestSimpleProduct(
                $configurableProduct,
                $localStore,
                $simpleProductCollection,
                $childItem->getProduct(),
                !$hasToReplace
            );

            if ($newProduct->getId() !== $childItem->getProductId()) {
                // Add product to the reserved list
                $this->_reserveVehicle($newProduct);

                /**
                 * Copy the buy request and change only the product. Assumption is that the products are identical hence
                 * options will match as well.
                 */
                $buyParams = $childItem->getBuyRequest() ? $childItem->getBuyRequest()->getData() : [];
                $buyParams['vehicleId'] = $newProduct->getId();
                unset($buyParams['original_qty']);

                /**
                 * Add product to cart
                 *
                 * Copy paste from Rockar_Checkout_CartController::addAction
                 */
                $cart = Mage::getSingleton('checkout/cart');
                $cart->truncate()
                    ->save();

                $cart->addProduct($configurableProduct, $buyParams)
                    ->save();

                $quote->setTotalsCollectedFlag(false);
                $quote->collectTotals()->save();
                $quote = Mage::getModel('sales/quote')->load($quote->getId());
                Mage::getSingleton('checkout/session')->replaceQuote($quote);
                Mage::helper('rockar_checkout')->resetQuoteItem();
                $quote->setTotalsCollectedFlag(false);
                $quote->collectTotals()->save();

                return true;
            }
        } else {
             Mage::log('Issue with replacing stock product in checkout, quotes product or local store missing');
        }

        return false;
    }

    /**
     * Add product to the reserved vins table to avoid duplicates
     *
     * @param $product
     * @return $this
     * @throws Mage_Core_Exception
     */
    protected function _reserveVehicle($product)
    {
        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        $reservationModel = Mage::getModel('peppermint_leadtime/reservation');

        Mage::helper('peppermint_leadtime')->removeVinReservation($customerId);

        $reservation = $reservationModel->load($product->getData('sku'), 'vin_number');

        if ($reservation->isEmpty() || $reservation->getCustomerId() == $customerId) {
            $reservation->addData(
                [
                    'vin_number' => $product->getData('sku'),
                    'customer_id' => $customerId,
                    'created_at' => Varien_Date::now()
                ]
            );

            $reservation->save();
        } else {
            throw new Exception(
                $this->__('The Vehicle You Have Selected Is No Longer Available | Please Select Another Vehicle'),
                Peppermint_Checkout_OnepageController::EXCEPTION_OUT_OF_STOCK
            );
        }

        return $this;
    }

    /**
     * Check if product has lead time for it.
     *
     * @return bool
     */
    public function checkProductLeadTime()
    {
        return $this->getProductLeadTime() > 0;
    }

    /**
     * Protected get next simple product
     * =================================
     *
     * Equivalent function to getCheapestClosestSimpleProduct
     * but is protected for extension and will also throw errors
     *
     * @param Mage_Catalog_Model_Product $configurableProduct
     * @param Rockar_Localstores_Model_Stores $collectionLocation
     * @param null $simpleProducts
     * @param Mage_Catalog_Model_Product|null $currentProduct
     *
     * @param bool $allowCurrent
     * @return Mage_Catalog_Model_Product|null
     *
     * @throws Mage_Core_Exception
     */
    protected function _getCheapestClosestSimpleProduct(
        Mage_Catalog_Model_Product $configurableProduct,
        Rockar_Localstores_Model_Stores $collectionLocation = null,
        $simpleProducts = null,
        Mage_Catalog_Model_Product $currentProduct = null,
        $allowCurrent = true
    ) {
        if ($configurableProduct->getTypeId() !== Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            throw new Mage_Core_Exception('Passed product is not configurable.');
        }

        if (!$simpleProducts) {
            $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();

            $vinReservationCollection = Mage::getResourceModel('peppermint_leadtime/reservation_collection');
            $vinReservationCollection->getSelect()
                ->reset()
                ->where('customer_id != ?', $customerId)
                ->from($vinReservationCollection->getTable('peppermint_leadtime/reservation'), ['vin_number']);

            $simpleProducts = $configurableProduct->getTypeInstance()
                ->getUsedProductCollection($configurableProduct)
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                ->addFieldToFilter('sku', ['nin' => $vinReservationCollection->getSelect()]);
        }

        if ($simpleProducts->getSize() === 0) {
            return null;
        }

        $currentProduct = $currentProduct ?: $simpleProducts->getFirstItem();
        $id = $currentProduct->getId();

        if ($id) {
            $simpleProducts->addFieldToFilter('entity_id', ['neq' => $id]);
            // Need to reload the product to get bag_vehicle_location_dlr_code attribute as $currentProduct is loaded from quoteItem
            $currentProduct = Mage::getModel('catalog/product')->load($id);
        }

        $compareData = [];

        if ($allowCurrent) {
            $compareData = [
                'product' => $currentProduct,
                'distance' => $this->_getDistanceFromLocationToProduct($collectionLocation, $currentProduct),
                'price' => $this->_getFinalPrice($currentProduct)
            ];
        }

        foreach($simpleProducts as $nextProduct) {
            $nextPrice = $this->_getFinalPrice($nextProduct);

            if ($this->_checkBusinessRules($collectionLocation, $compareData, $nextProduct, $nextPrice)) {
                $compareData = [
                    'product' => $nextProduct,
                    'distance' => $this->_getDistanceFromLocationToProduct($collectionLocation, $nextProduct),
                    'price' => $nextPrice
                ];
            }
        }

        return $compareData['product'];
    }

    /**
     * Checks the business rules for next simple product
     *
     * @param Rockar_Localstores_Model_Stores $location
     * @param Array $compareData
     * @param Mage_Catalog_Model_Product $product
     * @param string|float $price
     *
     * @return bool
     */
    protected function _checkBusinessRules($location, $compareData, $product, $price = null)
    {
        $price = $price ?? $this->_getFinalPrice($product);

        if (empty($compareData['product']) || $price < $compareData['price']) {
            return true;
        }

        if ($price > $compareData['price']) {
            return false;
        }

        $distance = $this->_getDistanceFromLocationToProduct($location, $product);

        return !($distance >= $compareData['distance'] || ($location && $distance === -1));
    }

    /**
     * Gets the distance between a localstore location and a products stock location
     *
     * @param Rockar_Localstores_Model_Stores $location
     * @param Mage_Catalog_Model_Product $product
     *
     * @return number - -1 if distance can't be found
     */
    protected function _getDistanceFromLocationToProduct($location, $product)
    {
        $dealerCode = $product->getData('bag_vehicle_location_dlr_code');

        if (!$dealerCode || !$location) {
            return -1;
        }

        return Mage::helper('peppermint_localstores')->getDistanceBetweenStores(
            $location,
            Mage::getModel('rockar_localstores/stores')->load($dealerCode, 'dealer_code')
        );
    }

    /**
     * Gets simples product collection from current quote session if exist
     *
     * @param Mage_Sales_Model_Quote_Item $parentQuoteItem
     * @param Mage_Sales_Model_Quote_Item $childItem
     *
     * @return Object|null
     */
    protected function _getSimpleSiblingsCollection($parentQuoteItem, $childItem)
    {
        if ($parentQuoteItem->getProduct()) {
            $parentProductId = $parentQuoteItem->getProductId();
        } else {
            return null;
        }

        $configurableProduct = Mage::getModel('catalog/product')->setStoreId(Mage::app()->getStore()->getId())
            ->load($parentProductId);

        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();

        $vinReservationCollection = Mage::getResourceModel('peppermint_leadtime/reservation_collection');
        $vinReservationCollection->getSelect()
            ->reset()
            ->where('customer_id != ?', $customerId)
            ->from($vinReservationCollection->getTable('peppermint_leadtime/reservation'), ['vin_number']);

        $simpleProduct = $childItem->getProduct();

        $collection = $configurableProduct->getTypeInstance()
            ->getUsedProductCollection($configurableProduct)
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            ->addAttributeToFilter('visible_in', [
                'in' => [
                    Rockar_YouDrive_Model_Product_Attribute_Source_Visiblein::CATALOG,
                    Rockar_YouDrive_Model_Product_Attribute_Source_Visiblein::CATALOG_AND_YOUDRIVE
                ],
            ])
            ->addFieldToFilter('sku', ['nin' => $vinReservationCollection->getSelect()]);

        if ($simpleProduct) {
            $collection->addFieldToFilter('entity_id', ['neq' => $simpleProduct->getId()]);
        }

        return $collection;
    }

    /**
     * Get quote in checkout session
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getCheckoutSessQuote()
    {
        $cacheKey = 'sess_quote';

        if (!$this->_cacheData[$cacheKey]) {
            $this->_cacheData[$cacheKey] = Mage::getSingleton('checkout/session')->getQuote();
        }

        return $this->_cacheData[$cacheKey];
    }

    /**
     * Get product final price
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return float
     */
    protected function _getFinalPrice($product)
    {
        $quote = $this->_getCheckoutSessQuote();
        $custGroup = $quote->getCustomerGroupId();
        $storeId = $quote->getStoreId();

        if ($custGroup && $storeId) {
            // To get the correct final price we need to set store and
            // customer group to the product because of catalog price rules
            $product = Mage::getModel('catalog/product')->load($product->getId())
                ->setStoreId($storeId)
                ->setCustomerGroupId($custGroup);

            return (float) $product->getFinalPrice();
        }

        return (float) Mage::helper('peppermint_catalog/vehicle')->getFinalPrice($product);
    }

    /**
     * Return product lead time
     * Method of how to fetch lead time is the same as when fetching lead time on order placing in
     * Rockar_LeadTime_Model_Observer_Order::addLeadTimeInfo
     *
     * @return int
     */
    public function getProductLeadTime(): int
    {
        $quoteHelper = Mage::helper('rockar_checkout/quote');
        $leadTimeHelper = Mage::helper('rockar_lead_time');
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $quoteItem = $quoteHelper->getFirstVisibleQuoteItem($quote);
        $leadTime = 0;

        if ($quoteItem && $quoteItem->getProduct()) {
            $product = $quoteHelper->getFirstSimpleProduct($quote);
            $product = Mage::getModel('catalog/product')->load($product->getId());
            $leadTimeObj = $leadTimeHelper->getLeadTimeConfiguration($product);

            if ($leadTimeObj) {
                $leadTime = $leadTimeObj->getData('available_in');

                if (empty($leadTime)) {
                    $leadTime = $leadTimeHelper->getLeadTime($product);
                }
            }
        }

        return $leadTime;
    }

    /**
     * Replace product if necessary
     *
     * @param bool $alreadyReserved
     *
     * @return Varien_Object
     */
    public function expireQuoteProduct($alreadyReserved = false)
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $resultObj = new Varien_Object(
            [
                'success' => true,
                'vin_reserved' => $alreadyReserved
            ]
        );

        if (!$this->checkProductLeadTime() || $alreadyReserved) {
            Mage::dispatchEvent(
                'checkout_controller_onepage_place_order_vin_reserved',
                [
                    'result' => $resultObj,
                    'quote'  => $quote
                ]
            );
        }

        if ($resultObj->getSuccess()) {
            $response = [
                'error' => false,
                'redirect' => Mage::getUrl('checkout/onepage', ['_secure' => true]),
                'new_product_sku' => $resultObj->getProductSku(),
                'new_product_price' => $resultObj->getProductPrice(),
                'new_product_lead_time' => $resultObj->getProductLeadTime()
            ];

            if ($resultObj->getPriceChanged() || $resultObj->getLeadTimeChanged()) {
                $response = array_merge($response, [
                    'error' => true,
                    'message' => 'Product was changed',
                    'message_title' => $this->__('Please review the product data changes'),
                    'out_of_stock' => 1,
                ]);

                $blockIdentifier = $resultObj->getPriceChanged()
                    ? self::PRODUCT_EXPIRED_CHANGED_PRICE_BLOCK
                    : self::PRODUCT_EXPIRED_CHANGED_LEADTIME_BLOCK;
            }
        } else {
            $response  = [
                'error' => true,
                'message_title' => $this->__('Product no longer available'),
                'message' => $this->__('Product no longer available'),
                'out_of_stock' => 1,
                'redirect' => Mage::helper('peppermint_checkout')->getCarFinderUrl(true)
            ];
            $blockIdentifier = self::PRODUCT_EXPIRED_NO_REPLACE;
        }

        if (!empty($blockIdentifier) ) {
            $block = Mage::getModel('cms/block')->load($blockIdentifier, 'identifier');
            $html = '';

            if ($block->getIsActive()) {
                /* @var $helper Mage_Cms_Helper_Data */
                $processor = Mage::helper('cms')->getBlockTemplateProcessor();
                $processor->setVariables([
                    'before' => $resultObj->getChangedValueBefore(),
                    'after' => $resultObj->getChangedValueAfter()
                ]);
                $html = $processor->filter($block->getContent());
            }

            $response['message'] = $html;
            $response['message_title'] = $block->getTitle();
        }

        $resultObj->setResponse($response);

        return $resultObj;
    }
}

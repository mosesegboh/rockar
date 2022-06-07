<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Orderamend
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

/**
 * Class Peppermint_Orderamend_Helper_Accessories_Data
 */
class Peppermint_Orderamend_Helper_Accessories_Data extends Rockar_Orderamend_Helper_Accessories_Data
{
    /**
     * Get accessories for product in quote
     *
     * @param bool $encoded
     * @return object|string
     */

    public function getAccessoriesForProduct($encoded = false)
    {
        $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
        $quoteItem = Mage::helper('rockar_checkout/quote')->getFirstVisibleQuoteItem(
            $quote,
            Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
        );

        if (!$quoteItem) {
            $product = Mage::helper('peppermint_catalog/vehicle')->getConfigurableFromSimple(
                Mage::helper('rockar_checkout/quote')
                    ->getFirstVisibleQuoteItem($quote)
                    ->getProductId()
            );
        } else {
            $product = Mage::getModel('catalog/product')->load($quoteItem->getProduct()->getId());
        }

        $store = $quote->getStore();

        $accessories = [];

        if ($product->getId()) {
            /** @var $collection Rockar_Accessories_Model_Resource_Accessories_Collection */
            $collection = Mage::getModel('rockar_accessories/accessories')->getCollection()
                ->addEnabledFilter()
                ->addProductFilterBySku($product->getSku())
                ->addGroupDefaultOrder();

            $orderItem = Mage::helper('rockar_orderamend')->getFirstVisibleOrderItem();
            $savedAccessories = [];

            if ($product->getId() == $orderItem->getProductId()) {
                $savedAccessories = $this->getPreSelectedAccessoriesFromItem($orderItem);
            }

            $placeholderImage = Mage::helper('rockar_accessories')->getPlaceholder($store);

            foreach ($collection as $accessory) {
                if (!isset($accessories[$accessory->getData('accessories_group_id')])) {
                    $accessories[$accessory->getData('accessories_group_id')] = [
                        'id' => $accessory->getData('accessories_group_id'),
                        'group_id' => $accessory->getData('group_identifier'),
                        'name' => $accessory->getData('group_custom_name')
                            ? $accessory->getData('group_custom_name') : $accessory->getData('group_name'),
                        'product_id' => $product->getId(),
                        'accessories' => [],
                    ];
                }
                $accessoryId = $accessory->getData('id');

                $accessories[$accessory->getData('accessories_group_id')]['accessories'][$accessory->getData('id')] = [
                    'id' => $accessoryId,
                    'name' => $accessory->getFinalName(),
                    'price' => $accessory->getFinalPrice(),
                    'image' => $accessory->getImageFullUrl() ?: $placeholderImage,
                    'status' => isset($savedAccessories[$accessoryId]) ? 1 : 0,
                    'preSelected' => isset($savedAccessories[$accessoryId]['pre_selected'])
                        && $savedAccessories[$accessoryId]['pre_selected'] ?: false,
                    'extendedDescription' => nl2br($accessory->getFinalDescription())
                ];
            }

            foreach ($accessories as $id => $accessory) {
                $accessories[$id]['accessories'] = array_values($accessory['accessories']);
            }
        }

        $accessories = (object)$accessories;

        return $encoded ? Mage::helper('rockar_all')->jsonEncode($accessories) : $accessories;
    }

    /**
     * Prepare accessories for saving to quote
     *
     * @param $accessoriesIds
     * @return array
     */
    public function prepareAccessoriesForSaving($accessoriesIds)
    {
        $orderItem = Mage::helper('rockar_orderamend')->getFirstVisibleOrderItem();
        $product = Mage::getModel('catalog/product')->load($orderItem->getProduct()->getId());
        $savedAccessories = $this->getPreSelectedAccessoriesFromItem($orderItem);
        $notAvailableAccessoriesIds = [];

        foreach ($this->getNotAvailableAccessories() as $notAvailableAccessory) {
            $notAvailableAccessoriesIds[] = $notAvailableAccessory['id'];
        }

        /** @var $accessoriesCollection Rockar_Accessories_Model_Resource_Accessories_Collection */
        $accessoriesCollection = Mage::getModel('rockar_accessories/accessories')->getCollection()
            ->addFieldToFilter('main_table.id', ['in' => $accessoriesIds]);

        if ($product->getId()) {
            $accessoriesCollection->addEnabledFilter()
                ->addProductFilterBySku($product->getSku())
                ->addGroupDefaultOrder();
        }

        // add accessories
        foreach ($accessoriesCollection as $accessory) {
            if (!isset($savedAccessories[$accessory->getId()])) {
                $savedAccessories[$accessory->getId()] = $accessory->getData();
            }
        }

        if (is_null($accessoriesIds)) {
            $accessoriesIds = [];
        }

        // remove accessories but leave not available accessories in quote
        foreach ($savedAccessories as $id => $accessory) {
            if (!in_array($id, $accessoriesIds) && !in_array($id, $notAvailableAccessoriesIds)) {
                unset($savedAccessories[$id]);
            }
        }

        return Mage::helper('rockar_all')->jsonEncode($savedAccessories);
    }

    /**
     * Get not available accessories which are not active or deleted
     *
     * @return array
     */
    public function getNotAvailableAccessories()
    {
        $orderItem = Mage::helper('rockar_orderamend')->getFirstVisibleOrderItem();
        $product = Mage::getModel('catalog/product')->load($orderItem->getProduct()->getId());
        $savedAccessories = $this->getPreSelectedAccessoriesFromItem($orderItem);

        $notAvailableAccessories = [];

        if ($product->getId()) {
            /** @var $collection Rockar_Accessories_Model_Resource_Accessories_Collection */
            $collection = Mage::getModel('rockar_accessories/accessories')->getCollection()
                ->addEnabledFilter()
                ->addProductFilterBySku($product->getSku())
                ->addGroupDefaultOrder();
            $accessoriesIds = $collection->getAllIds();

            foreach ($savedAccessories as $savedAccessory) {
                if (!in_array($savedAccessory['id'], $accessoriesIds)) {
                    $notAvailableAccessories[] = $savedAccessory;
                }
            }
        }

        return $notAvailableAccessories;
    }
}

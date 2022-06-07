<?php
/**
 * @category     Peppermint
 * @package      Peppermint_Catalog
 * @author       Dominic Sutton <dominic.sutton@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
*/
class Peppermint_Catalog_Helper_Data extends Rockar_Catalog_Helper_Data
{
    /**
     * Returns product Accesories from custom option
     *
     * @param $product
     * @return Array
     */
    public function getCustomOptionAccessories($product, $collection = false)
    {
        $options = Mage::helper('rockar_catalog/vehicle')->getOptionsData($product);
        $accessoriesSku = [];

        foreach ($options['options'] as $groupTitle => $group) {
            if ($groupTitle == 'Accessories') {
                foreach ($group as $key => $value) {
                    if (isset($value['sku'])) {
                        $accessoriesSku[] = $value['sku'];
                    }
                }
            }
        }

        $accessories = [];

        if (!empty($accessoriesSku)) {
            if (!$collection) {
                $parentApiKey = $this->_getParentApiKey($product->getJlrApiKey());

                /** @var $collection Jlr_Accessories_Model_Resource_Accessories_Collection */
                $collection = Mage::getModel('rockar_accessories/accessories')->getCollection();
                $collection->addEnabledFilter();
                $collection->addProductFilterByParentApiKey($parentApiKey);
                $collection->addGroupDefaultOrder();

                foreach ($collection as $accessory) {
                    foreach ($accessoriesSku as $sku) {
                        if ($accessory->getData('identifier') == strtolower($sku)) {
                            $accessories[$accessory->getData('id')] = [
                                'id' => $accessory->getData('id'),
                                'accessories_group_id' => $accessory->getData('accessories_group_id'),
                                'identifier' => $accessory->getData('identifier'),
                                'name' => $accessory->getFinalName(),
                                'price' => $accessory->getFinalPrice(),
                                'pre_selected' => true
                            ];
                        }
                    }
                }
           } else {
              foreach ($collection as $accessory) {
                    foreach ($accessoriesSku as $sku) {
                        if ($accessory->getData('identifier') == strtolower($sku)) {
                            $accessories[$accessory->getData('id')] = [
                                'id' => $accessory->getData('id'),
                                'accessories_group_id' => $accessory->getData('accessories_group_id'),
                                'identifier' => $accessory->getData('identifier'),
                                'name' => $accessory->getFinalName(),
                                'price' => $accessory->getFinalPrice(),
                                'pre_selected' => true
                            ];
                        }
                    }
                }
            }
        }

        return $accessories;
    }

    /**
     * Extract API key for the parent product (one with default set of features)
     *
     * @param $apiKey
     * @return string
     */
    private function _getParentApiKey($apiKey): string
    {
        $parentApiKey = explode('/', $apiKey, 8);
        if (\count($parentApiKey) === 8) {
            $parentApiKey[7] = '';
            unset($parentApiKey[5]);
        }

        return implode('/', $parentApiKey);
    }

    /**
     * Get Product TypeId from list of productIds as key value pair
     *
     * @param array $productIds
     * @return array
     */
    public function getProductTypeIds(array $productIds)
    {
        $coreResource = Mage::getSingleton('core/resource');
        $readConnection = $coreResource->getConnection('core_read');

        $select = $readConnection->select()
            ->from($coreResource->getTableName('catalog/product'), ['entity_id', 'type_id'])
            ->where('entity_id IN(?)', $productIds);

        return $readConnection->fetchPairs($select);
    }

    /**
     * Get product OfferTag
     *
     * @return string
     */
    public function getOfferTagData($product)
    {
        $result = [];
        $financeHelper = Mage::helper('financing_options');

        $offerTags = $product->getOfferTags();

        if ($offerTags) {
            $activePayment = $financeHelper->getActivePayment();
            $groupId = $activePayment['group_id'];

            $result = $offerTags[$groupId] ?? '';
        }

        return Mage::helper('rockar_all')->jsonEncode($result);
    }
}

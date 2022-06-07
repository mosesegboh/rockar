<?php

/**
 * @category     Peppermint
 * @package      Peppermint_Catalog
 * @author       Dominic Sutton <dominic.sutton@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Catalog_Block_Product_View extends Rockar2_Catalog_Block_Product_View
{
    const ATTR_EXTERIOR_CODE = 'exterior';

    /**
     * Get color data json
     *
     * @return array
     */
    public function getColorJson()
    {
        return json_encode([], JSON_FORCE_OBJECT);
    }

    /**
     * Get exterior color
     *
     * @return string|null
     */
    public function getExteriorColor()
    {
        $product = $this->getProduct();
        $attributes = $product->getAttributes();
        $exteriorAttr = isset($attributes[self::ATTR_EXTERIOR_CODE]) ? $attributes[self::ATTR_EXTERIOR_CODE] : null;

        if (!$exteriorAttr) {
            return null;
        }

        $label = $product->getAttributeText(self::ATTR_EXTERIOR_CODE);
        $image = $this->getAttributeImage($exteriorAttr->getSource()->getAllOptions(false), $label);

        return (new Varien_Object(['value' => $label, 'image' => $image]))->toJson();
    }

    /**
     * Get an Image based on attribute option label
     *
     * @param string $label
     * @return string
     */
    private function getAttributeImage($options, $label)
    {
        foreach ($options as $option) {
            if ($option['label'] == $label) {
                return $option['image'];
            }
        }

        return '';
    }

    /**
     * Get product json data
     *
     * @return string
     */
    public function getProductJson()
    {
        $result = [];
        $vehicleHelper = Mage::helper('rockar_catalog/vehicle');
        $compareHelper = Mage::helper('rockar_compare');
        $wishlistHelper = Mage::helper('rockar_mysavedcars');

        /** @to-do review correct title, short title usage */
        if ($product = $this->getProduct()) {
            $result = [
                'id' => (int) $product->getId(),
                'sku' => $product->getSku(),
                'name' => $product->getName(),
                'title' => $vehicleHelper->getTitle($product),
                'short_title' => $vehicleHelper->getSubTitle($product),
                'bodystyle' => $product->getAttributeText('bodystyle') ?: '',
                'short_description' => $product->getShortDescription(),
                'customerName' => Mage::helper('rockar_mysavedcars')->getCustomerName(),
                'myAccountUrl' => $this->getUrl('customer/account'),
                'isWishlistAjax' => Mage::getSingleton('customer/session')->isLoggedIn(),
                'removeFromWishlistUrl' => $wishlistHelper->getRemoveByProductIdUrl((int) $product->getId()),
                'compareAddUrl' => $compareHelper->getAddUrl($product->getId()),
                'compareRemoveUrl' => $compareHelper->getRemoveUrl($product),
                'isInCompareList' => $compareHelper->isInCompareList($product->getId()),
                'childProductId' => $vehicleHelper->getFirstVehicle($product)
                    ? (int) $vehicleHelper->getFirstVehicle($product)->getId()
                    : false,
                'color' => $product->getData('color') ? $product->getAttributeText('color') : '',
            ];

            $dataObject = new Varien_Object($result);
            Mage::dispatchEvent('rockar_catalog_prepare_product_data', ['data' => $dataObject, 'product' => $product]);
            $result = $dataObject->toArray();
        }

        return Mage::helper('rockar_all')->jsonEncode($result);
    }

    /**
     * Returns YouDrive link, having active product pre-selected in YouDrive slider
     *
     * @return string
     */
    public function getYouDriveLink()
    {
        return $this->getUrl(
            'test-drives',
            [
                '_query' => [
                    'modelIds' => $this->getProduct()->getData(Mage::helper('rockar_all')->getModelAttribute())
                ]
            ]
        );
    }

    /**
     * Get product Attributes
     *
     * @return array
     */
    public function getCarAttributes()
    {
        $result = [];

        if ($product = $this->getProduct()) {
            $carAttributes = Mage::helper('peppermint_catalog/attributes')->mapCarAttributes($product);

            $attributeData = [
                'km' => $carAttributes['km'],
                'fuel_type' => $carAttributes['fuel_type'],
                'transmission' => $carAttributes['transmission'],
                'vehicle_condition' => $carAttributes['vehicle_condition'],
                'ec_combined_mpg' => $carAttributes['ec_combined_mpg'],
                'engine_power' => $carAttributes['engine_power'],
                'n_0_to_100' => $carAttributes['n_0_to_100'],
                'torque_only' => $carAttributes['torque_only'],
                'engine_capacity' => $carAttributes['engine_capacity']
            ];

            $productDataObject = new Varien_Object($attributeData);
            $result = $productDataObject->toArray();
        }

        return Mage::helper('rockar_all')->jsonEncode($result);
    }

    /**
     * gets accessories json for provided product
     *
     * @return string
     */
    public function getAccessoriesJson()
    {
        /** @var $collection Rockar_Accessories_Model_Resource_Accessories_Collection */
        $collection = Mage::getModel('rockar_accessories/accessories')->getCollection()
            ->addEnabledFilter()
            ->addProductFilterBySku($this->getProduct()->getSku())
            ->addGroupDefaultOrder();

        $rockarAccessories = Mage::helper('rockar_accessories');
        $wishlistAccessories = $rockarAccessories->getSelectedAccessoriesPerProduct(
                $this->getProduct()->getId(),
                Rockar_Accessories_Helper_Data::SESSION_ACCESSORIES_WISHLIST_KEY
        );

        if ($wishlistAccessories) {
            $savedAccessories = $wishlistAccessories;
        } else {
            $savedAccessories = $rockarAccessories->getSelectedAccessoriesPerProduct(
                $this->getProduct()->getId());
        }

        $placeholderImage = $rockarAccessories->getPlaceholder(Mage::app()->getStore());

        $accessories = [];

        if (Mage::helper('peppermint_all/newJourney')->isBrandSelected()) {
            foreach ($collection as $accessory) {
                $accessories[$accessory->getData('id')] = [
                    'id' => $accessory->getData('id'),
                    'name' => $accessory->getFinalName(),
                    'price' => $accessory->getFinalPrice(),
                    'image' => $accessory->getImageFullUrl() ?: $placeholderImage,
                    'status' => isset($savedAccessories[$accessory->getData('id')]) ? 1 : 0,
                    'extendedDescription' => nl2br($accessory->getFinalDescription()),
                    'group_id' => $accessory->getData('accessories_group_id'),
                    'group_identifier' => $accessory->getData('group_identifier'),
                    'group_name' => $accessory->getData('group_custom_name') ?: $accessory->getData('group_name'),
                    'product_id' => $this->getProduct()->getId(),
                ];
            }
        } else {
            foreach ($collection as $accessory) {
                if (!isset($accessories[$accessory->getData('accessories_group_id')])) {
                    $accessories[$accessory->getData('accessories_group_id')] = [
                        'id' => $accessory->getData('accessories_group_id'),
                        'group_id' => $accessory->getData('group_identifier'),
                        'name' => $accessory->getData('group_custom_name') ?: $accessory->getData('group_name'),
                        'product_id' => $this->getProduct()->getId(),
                        'accessories' => [],
                    ];
                }

                $accessories[$accessory->getData('accessories_group_id')]['accessories'][$accessory->getData('id')] = [
                    'id' => $accessory->getData('id'),
                    'name' => $accessory->getFinalName(),
                    'price' => $accessory->getFinalPrice(),
                    'image' => $accessory->getImageFullUrl() ?: $placeholderImage,
                    'status' => isset($savedAccessories[$accessory->getData('id')]) ? 1 : 0,
                    'extendedDescription' => nl2br($accessory->getFinalDescription())
                ];
            }

            foreach ($accessories as $id => $accessory) {
                $accessories[$id]['accessories'] = array_values($accessory['accessories']);
            }
        }

        return Mage::helper('rockar_all')->jsonEncode(array_values($accessories));
    }

    /**
     * gets accessories groups json for provided product
     *
     * @return string
     */
    public function getAccessoriesGroupJson()
    {
        $collection = Mage::getModel('rockar_accessories/accessories')->getCollection()
            ->addEnabledFilter()
            ->addProductFilterBySku($this->getProduct()->getSku())
            ->addGroupDefaultOrder();
        $groups = [];

        foreach ($collection as $accessory) {
            if (!isset($groups[$accessory->getData('accessories_group_id')])) {
                $groups[$accessory->getData('accessories_group_id')] = [
                    'group_id' => $accessory->getData('accessories_group_id'),
                    'group_identifier' => $accessory->getData('group_identifier'),
                    'group_name' => $accessory->getData('group_custom_name') ?: $accessory->getData('group_name'),
                ];
            }
        }

        return Mage::helper('rockar_all')->jsonEncode(array_values($groups));
    }

    /**
     * Get all extra cosy images
     *
     * @param array Finance quote data
     * @return string
     */
    public function getAllCosyImagesAsJson($financeQuoteData)
    {
        $product = $this->_getActiveVehicle();
        $result = Mage::helper('rockar_catalog/images')->getAllCosyImagesAsJson($product, $financeQuoteData);

        return $result;
    }
}

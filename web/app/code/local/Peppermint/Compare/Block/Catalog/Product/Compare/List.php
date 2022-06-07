<?php
/**
 * @category     Peppermint
 * @package      Peppermint_Compare
 * @author       Dominic Sutton <dominic.sutton@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
*/
class Peppermint_Compare_Block_Catalog_Product_Compare_List extends Rockar2_Compare_Block_Catalog_Product_Compare_List
{
    const GROUP_KEY_DETAILS = 0;
    const GROUP_ENGINE_AND_PERFORMANCE = 1;
    const GROUP_FUEL_ECONOMY = 2;
    const GROUP_WEIGHT_AND_CAPACITIES = 3;
    const GROUP_STANDARD_FEATURES = 4;
    const GROUP_OTHER_FEATURES = 5;

    protected $_attributeGroupNames = [
        self::GROUP_KEY_DETAILS            => 'Key Details',
        self::GROUP_ENGINE_AND_PERFORMANCE => 'Engine & Performance',
        self::GROUP_FUEL_ECONOMY           => 'Fuel Economy',
        self::GROUP_WEIGHT_AND_CAPACITIES  => 'Weights & Capacities',
        self::GROUP_STANDARD_FEATURES      => 'Standard Features',
        self::GROUP_OTHER_FEATURES         => 'Other Features',
    ];

    /**
     * Default groups for the attributes
     *
     * @var array
     */
    protected $_attributeGroups = [
        'road_fund_licence_cost'       => self::GROUP_KEY_DETAILS,
        'road_fund_licence'            => self::GROUP_KEY_DETAILS,
        'seats'                        => self::GROUP_KEY_DETAILS,
        'luggage_capacity'             => self::GROUP_KEY_DETAILS,
        'insurance_group'              => self::GROUP_KEY_DETAILS,
        'miles'                        => self::GROUP_KEY_DETAILS,
        'au_model_year'                => self::GROUP_KEY_DETAILS,
        'registration_year'            => self::GROUP_KEY_DETAILS,
        'vehicle_condition'            => self::GROUP_KEY_DETAILS,
        'engine_power'                 => self::GROUP_ENGINE_AND_PERFORMANCE,
        'top_speed'                    => self::GROUP_ENGINE_AND_PERFORMANCE,
        'n_0_to_100'                   => self::GROUP_ENGINE_AND_PERFORMANCE,
        'ec_combined_mpg'              => self::GROUP_FUEL_ECONOMY,
        'ec_urban_mpg'                 => self::GROUP_FUEL_ECONOMY,
        'ec_extra_urban_mpg'           => self::GROUP_FUEL_ECONOMY,
        'emission'                     => self::GROUP_FUEL_ECONOMY,
        'wheelbase'                    => self::GROUP_WEIGHT_AND_CAPACITIES,
        'height'                       => self::GROUP_WEIGHT_AND_CAPACITIES,
        'length'                       => self::GROUP_WEIGHT_AND_CAPACITIES,
        'width'                        => self::GROUP_WEIGHT_AND_CAPACITIES,
        'width_including_mirrors'      => self::GROUP_WEIGHT_AND_CAPACITIES,
        'fuel_tank_capacity_in_litres' => self::GROUP_WEIGHT_AND_CAPACITIES,
        'max_towing_weight_unbraked'   => self::GROUP_WEIGHT_AND_CAPACITIES,
        'max_towing_weight_braked'     => self::GROUP_WEIGHT_AND_CAPACITIES,
        'minimum_kerbweight'           => self::GROUP_WEIGHT_AND_CAPACITIES,
        'luggage_capacity_seats_up'    => self::GROUP_WEIGHT_AND_CAPACITIES,
        'standard_features'            => self::GROUP_STANDARD_FEATURES,
    ];

    /**
     * Attributes that have images
     *
     * @var array
     */
    protected $_attributesWithImages = [
        'wheel',
        'exterior',
        'interior',
        'trim_finisher'
    ];

    /**
     * Necessary attributes from finance car data
     *
     * @var array
     */
    protected $_financeCarDataAttributes = [
        'Line/Packages',
        'Extra Options'
    ];

    /**
     * Technical specs to remove from technical spec data
     *
     * @var array
     */
    protected $_techSpecToRemove = [
        'Tyre Dimensions',
        'Wheelbase (mm)',
        'Rear wheel track (mm)'
    ];

    /**
     * Fill compare items data into JSON structure
     *
     * @param $items
     * @param $attributes
     * @return array
     * @throws Exception
     */
    protected function _processItems($items, $attributes)
    {
        $result = [];
        $helper = Mage::helper('rockar_compare');
        $vehicleHelper = Mage::helper('rockar_catalog/vehicle');
        $carFinderHelper = Mage::helper('rockar_catalog/carFinder');
        $wishlistHelper = Mage::helper('rockar_mysavedcars');

        foreach ($items as $item) {
            $itemId = $item->getId();
            $configuration = $this->_getCheapestConfiguration($itemId);

            if ($item->getTypeId() !== Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
                if ($configuration === false) {
                    throw new Exception($this->__('No available configuration for the product "%s"', $item->getSku()));
                }
            }else{
                $configuration = $item;
            }

            $product = Mage::getModel('catalog/product')->load($itemId);

            $financeData = $this->getDataFromFinanceQuote($product);
            $standardFeatures = json_decode(Mage::helper('rockar_catalog/attributes')->getStandardFeatures($product));

            $result[$itemId] = [
                'id' => $itemId,
                'name' => $this->stripTags($item->getName(), null, true),
                'title' => $this->stripTags($vehicleHelper->getTitle($item), null, true),
                'subTitle' => $this->stripTags($vehicleHelper->getSubTitle($item), null, true),
                'productUrl' => $this->getProductUrl($item),
                'removeUrl' => $helper->getRemoveUrl($item),
                'image' => Mage::helper('peppermint_catalog/images')->getSmallImage($item),
                'price' => $vehicleHelper->getFinalPriceWithOptions($configuration),
                'configurationId' => $configuration->getId(),
                'youDriveIndex' => $item->getData(Mage::helper('rockar_rules_engine')->getProductModelAttributeName()),
                'visibleInYouDrive' => Mage::helper('rockar_youdrive')->hasYouDriveCars($item, true),
                'saveUrl' => $wishlistHelper->getSaveUrl($item->getId()),
                'removeFromWishlistUrl' => $wishlistHelper->getRemoveByProductIdUrl($item->getId()),
                'isSaved' => (bool) $wishlistHelper->isInWishlist($configuration->getId(), true),
                'sku' => $item->getSku(),
                'monthlyPrice' => $financeData['monthly_price'],
                'cash' => $financeData['is_pay_in_full'],
                'interiorImage' => $this->getInteriorImage($item),
                'technicalSpecs' => $this->getTechnicalData($product),
                'standardFeatures' => array_key_exists(0, $standardFeatures) ? $standardFeatures[0]->features : [],
                'linePackages' => $financeData['Line/Packages'],
                'extraOptions' => $financeData['Extra Options'],
                'compare' => json_decode(Mage::helper('peppermint_catalog')->getOfferTagData($product), true)
            ];

            foreach ($attributes as $attribute) {
                $attributeCode = $attribute->getAttributeCode();
                $result[$itemId][$attributeCode] =
                    $this->helper('catalog/output')->productAttribute($item,
                        $this->getProductAttributeValue($item, $attribute), $attributeCode);

                if (in_array(strtolower($result[$itemId][$attributeCode]), $this->_notAvailable)) {
                    if ($item->getTypeId() === Mage_Catalog_Model_Product_Type::TYPE_SIMPLE
                        && isset(Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($item->getId())[0])) {
                        $configurableItem = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($item->getId())[0];
                        $configurableItem = Mage::getModel('catalog/product')->load($configurableItem);
                        $result[$itemId][$attributeCode] =
                            $this->helper('catalog/output')->productAttribute($configurableItem,
                                $this->getProductAttributeValue($configurableItem, $attribute), $attributeCode);
                    }

                    if (in_array(strtolower($result[$itemId][$attributeCode]), $this->_notAvailable)) {
                        $result[$itemId][$attributeCode] = $this->__('Not available');
                    }
                } else if (in_array($attributeCode, $this->_attributesWithImages)) {
                    $attributeOptions = $attribute->getSource()->getAllOptions(false);
                    $attributeLabel = html_entity_decode(html_entity_decode($result[$itemId][$attributeCode]));
                    $camelCaseCode = lcfirst(implode('', array_map('ucfirst', explode('_', $attributeCode))));
                    $result[$itemId][$camelCaseCode . 'WithImages'] = [
                        'value' => $attributeLabel,
                        'image' => $this->getAttributeImage($attributeOptions, $attributeLabel)
                    ];
                }
            }

            $processedItem = new Varien_Object($result[$itemId]);

            Mage::dispatchEvent('rockar_compare_item_list_process', [
                'item' => $item,
                'processed_item' => $processedItem
            ]);

            $result[$itemId] = $processedItem->toArray();
        }

        return $result;
    }


    /**
     * Collect car compare data as JSON
     *
     * @return mixed
     */
    public function getCompareDataJson()
    {
        $result = [];

        $items = $this->getItems()->addAttributeToSelect('visible_in');
        $attributes = $this->getAttributes();

        // Resetting array indexes
        $result['items'] = array_values($this->_processItems($items, $attributes));
        $result['attributes'] = $this->_groupAttributes($attributes);
        $result['canAdd'] = $items->getSize() < Mage::helper('rockar_compare')->getCompareLimit();

        return Mage::helper('rockar_all')->jsonEncode($result);
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
     * Get Interior image
     *
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    private function getInteriorImage($product)
    {
        $type = Rockar_Catalog_Helper_Images::IMAGE_TYPE_INTERIOR;
        $images = Mage::helper('rockar_catalog/images')->getProductMedia($product);

        return isset($images[$type]) ? $images[$type][0]['image'] : '';
    }

    /**
     * Get necessary data from finance quote data
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    private function getDataFromFinanceQuote($product)
    {
        $financeData = $this->getFinanceQuoteData($product);
        $carData = json_decode($financeData['car_data']);
        $result = [];

        foreach ($carData as $value) {
            if (in_array($value->group, $this->_financeCarDataAttributes)) {
                $result = array_merge($result, [ $value->group => $value->items ]);
            }
        }

        return array_merge(
            $result, [
                'monthly_price' => (float) $financeData['monthly_price'],
                'is_pay_in_full' => (bool) $financeData['is_pay_in_full']
            ]
        );
    }

    /**
     * Return Finance Quote Data
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    private function getFinanceQuoteData($product)
    {
        $helper = Mage::helper('financing_options');

        $savedFinanceData = $helper->getSavedFinanceData();
        $ids = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());

        if ($product->getTypeId() === Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
            $ids = [$product->getId()];
        }

        $accessories = Mage::helper('rockar_accessories')->getSelectedAccessoriesPerProduct(array_shift($ids));

        $financeData = Mage::helper('peppermint_financingoptions/finance_quote')->getFinanceQuoteData(
            $product,
            $savedFinanceData,
            $accessories,
            Rockar_FinancingOptions_Model_Calculation_Type_Abstract::CALC_TYPE_PDP
        );

        return $financeData;
    }

    /**
     * Return technical clean technical data
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    private function getTechnicalData($product)
    {
        $technicalSpecs = json_decode(Mage::helper('rockar_catalog/attributes')->getTechnicalSpecifications($product));

        foreach ($technicalSpecs as &$value) {
            if ($value->title === 'DIMENSIONS') {
                foreach ($value->items as $key => $spec) {
                    if (in_array($spec->title, $this->_techSpecToRemove)) {
                        unset($value->items->$key);
                    }
                }
            }
        }

        return $technicalSpecs;
    }
}

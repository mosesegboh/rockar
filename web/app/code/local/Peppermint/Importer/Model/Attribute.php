<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Importer
 * @author    Razvan Zofota <razvan.zofota@rockar.com>, Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Importer_Model_Attribute extends Mage_Core_Model_Abstract
{
    /**
     * Store Codes.
     */
    const BMW_STORE_CODE = 'bmw_store_view'; // BMW Store
    const MIN_STORE_CODE = 'mini_store_view'; // Mini Store
    const MOT_STORE_CODE = 'motorrad_store_view'; // BMW Motorrad Store

    /**
     * Collection of attributes that dont't update their 'image' and 'thumb' options.
     * @var array
     */
    protected $_attributeImagesToIgnore = [
        'bmw_model_carousel',
        'min_model_carousel',
        'mot_model_carousel'
    ];

    /**
     * A list of all attribute codes that can have their options imported.
     *
     * @var string[]
     */
    protected $_attributeCodes = [
        'bodystyle',
        'fuel_type',
        'bmw_series',
        'min_series',
        'mot_series',
        'bmw_engine',
        'min_engine',
        'mot_engine',
        'bmw_range',
        'min_range',
        'mot_range',
        'doors',
        'front_brake_type',
        'rear_brake_type',
        'seats',
        'transmission',
        'wheel_dimensions',
        'wheel_type',
        'bag_plant_code',
        'bag_build_destination',
        'color',
        'model_code',
        'exterior',
        'interior',
        'wheel',
        'trim_finisher',
        'manufacturer',
        'vehicle_condition',
        'variant',
        'bmw_model_carousel',
        'min_model_carousel',
        'mot_model_carousel',
        'bmw_mm_description',
        'min_mm_description',
        'mot_mm_description',
        'bmw_model_range',
        'min_model_range',
        'mot_model_range',
        'cyl',
        'bmw_model_matrix_carousel',
        'min_model_matrix_carousel',
        'mot_model_matrix_carousel'
    ];

    /**
     * A list of attribute ids mapped by attribute codes.
     *
     * @var integer[]
     */
    protected $_attributeIds = [];

    /**
     * All options mapped by attribute id.
     *
     * @var Mage_Eav_Model_Entity_Attribute_Option[]|[]
     */
    protected $_attributeOptions = [];

    /**
     * @var integer
     */
    protected $_bmwStoreId;

    /**
     * @var integer
     */
    protected $_minStoreId;

    /**
     * @var integer
     */
    protected $_motStoreId;

    /**
     * @var integer[]
     */
    protected $_brands = [];

    /**
     * @var integer[]
     */
    protected $_allStoreIds = [];

    /**
     * Inits the store collections based on enabled stores.
     */
    public function __construct()
    {
        parent::__construct();

        $stores = Mage::app()->getStores(false, true);

        foreach ($stores as $storeCode => $store) {
            $storeId = (int) $store->getId();
            $this->_allStoreIds[] = $storeId;

            switch ($storeCode) {
                case self::BMW_STORE_CODE:
                    $this->_bmwStoreId = $storeId;
                    $this->_brands[$this->_bmwStoreId] = 'BMW';
                    break;

                case self::MIN_STORE_CODE:
                    $this->_minStoreId = $storeId;
                    $this->_brands[$this->_minStoreId] = 'MIN';
                    break;

                case self::MOT_STORE_CODE:
                    $this->_motStoreId = $storeId;
                    $this->_brands[$this->_motStoreId] = 'MOT';
                    break;
            }
        }
    }

    /**
     * Lowers and trims a given string.
     *
     * @param string $value
     * @return string
     */
    protected function _lowerTrim($value)
    {
        return trim(strtolower($value));
    }

    /**
     * Loads all attributes based on defined attribute codes.
     *
     * @return Peppermint_Importer_Model_Attribute
     */
    protected function _loadAttributes()
    {
        /** @var Mage_Eav_Model_Resource_Entity_Attribute_Collection $attributeCollection */
        $attributeCollection = Mage::getModel('eav/entity_attribute')->getCollection()
            ->addFieldToSelect(['attribute_id', 'attribute_code'])
            ->addFieldToFilter('attribute_code', ['in' => $this->_attributeCodes])
            ->load();

        /** @var Mage_Eav_Model_Entity_Attribute $attribute */
        foreach ($attributeCollection as $attribute) {
            $this->_attributeIds[$attribute->getAttributeCode()] = (int) $attribute->getId();
        }

        return $this;
    }

    /**
     * Loads all attributes options based on defined attribute ids.
     *
     * @return Peppermint_Importer_Model_Attribute
     */
    protected function _loadAttributesOptions()
    {
        $this->_attributeOptions = array_fill_keys($this->_attributeIds, []);
        /** @var Rockar_Catalog_Model_Resource_Eav_Entity_Attribute_Option_Collection $optionCollection */
        $optionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')->setPositionOrder('asc')
            ->addFieldToFilter('attribute_id', ['in' => $this->_attributeIds])
            ->setStoreFilter(Mage_Core_Model_App::ADMIN_STORE_ID)
            ->load();

        /** @var Mage_Eav_Model_Entity_Attribute_Option $option */
        foreach ($optionCollection as $option) {
            $this->_attributeOptions[$option->getAttributeId()][$this->_lowerTrim($option->getValue())] = $option;
        }

        return $this;
    }

    /**
     * Groups all options data by an unique value key.
     *
     * @param [] $options
     * @param string $attributeCode
     * @return []
     */
    protected function _prepareOptionsData($options, $attributeCode)
    {
        $preparedOptions = [];

        foreach ($options as $option) {
            if (empty($option)) {
                continue;
            }

            if (is_array($option)) {
                $optionKey = $this->_lowerTrim($option['id']);

                $preparedOptions[$optionKey] = [
                    'value' => $this->_fillNameForAllBrands(
                        $option['id'],
                        $option['text']
                    )
                ];

                if (!in_array($attributeCode, $this->_attributeImagesToIgnore)) {
                    $preparedOptions[$optionKey] = array_merge(
                        $preparedOptions[$optionKey],
                        [
                            'thumb' => $option['image'],
                            'image' => $option['image']
                        ]
                    );
                }
            } else {
                $optionKey = $this->_lowerTrim($option);
                $preparedOptions[$optionKey] = ['value' => [$option]];
            }
        }

        return $preparedOptions;
    }

    /**
     * Prepares an array with option values for each store.
     *
     * @param string $adminName
     * @param string $allOthersName
     * @return array
     */
    protected function _fillNameForAllBrands($adminName, $allOthersName)
    {
        return [Mage_Core_Model_App::ADMIN_STORE_ID => $adminName]
            + array_fill_keys(array_keys($this->_brands), $allOthersName);
    }

    /**
     * Updates all options for a given attribute id.
     *
     * @param integer $attributeId
     * @param [] $optionsData
     * @return Peppermint_Importer_Model_Attribute
     */
    protected function _updateOptions($attributeId, $optionsData)
    {
        $allOptions = [];

        foreach ($optionsData as $optionData) {
            $allOptions = array_replace_recursive($allOptions, $optionData);
        }

        Mage::getModel('catalog/resource_eav_attribute')->load($attributeId)
            ->setData('option', $allOptions)
            ->save();

        return $this;
    }

    /**
     * Merges all the options into one bigger data structure in order to be saved in a single save.
     *
     * @param [] $preparedOptions
     * @param [] $newOptions
     * @param Mage_Eav_Model_Entity_Attribute_Option[] $existingOptions
     * @return []
     */
    protected function _mergeAllOptions($preparedOptions, $newOptions, $existingOptions)
    {
        $optionsData = [];

        foreach ($newOptions as $newOptionKey => $newOption) {
            $optionsData[$newOptionKey] = $this->_addOptionId($newOption, 'option_' . $newOptionKey);
        }

        foreach ($existingOptions as $existingOptionKey => $existingOption) {
            if (isset($preparedOptions[$existingOptionKey])) {
                // Check if current hide in store options if exists do not override
                $hideInStores = $existingOption->getHideInStores();

                if ($hideInStores) {
                    $preparedOptions[$existingOptionKey]['hidden'] = explode(',', (string) $hideInStores);
                }

                $optionsData[$existingOptionKey] = $this->_addOptionId($preparedOptions[$existingOptionKey], (int) $existingOption->getId());
            }
        }

        return $optionsData;
    }

    /**
     * Wraps array values by the provided id.
     *
     * @param [] $option
     * @param mixed $identifier
     * @return []
     */
    protected function _addOptionId($option, $identifier)
    {
        // The data structure assembled below is expected by Peppermint_Carfinder_Model_Catalog_Resource_Eav_Mysql4_Attribute
        array_walk(
            $option,
            function (&$value) use ($identifier) {
                $value = [$identifier => $value];
            }
        );

        return $option;
    }

    /**
     * Goes through each attribute entity and imports options.
     *
     * @param string $message
     * @throws Mage_Core_Exception
     * @return void
     */
    public function processAttributeOptions($message)
    {
        if (empty($message)) {
            Mage::throwException('Message empty, expecting a json collection of attribute options!');
        }
        $jsonAttributesData = json_decode($message, true);

        if (!$jsonAttributesData) {
            Mage::throwException('Json cannot be decoded');
        }
        $this->_loadAttributes()
            ->_loadAttributesOptions();

        foreach ($jsonAttributesData as $attributeCode => $options) {
            $attributeId = $this->_attributeIds[$attributeCode];
            $existingOptions = $this->_attributeOptions[$attributeId] ?? [];
            $preparedOptions = $this->_prepareOptionsData($options, $attributeCode);
            $newOptions = array_diff_key($preparedOptions, $existingOptions);

            $optionsData = $this->_mergeAllOptions($preparedOptions, $newOptions, $existingOptions);
            $this->_updateOptions($attributeId, $optionsData);
        }
    }
}

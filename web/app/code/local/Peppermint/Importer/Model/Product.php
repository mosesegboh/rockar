<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Importer
 * @author    Adrian Grigorita <adrian.grigorita@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Importer_Model_Product extends Mage_Core_Model_Abstract
{
    /**
     * Vehicle visible_in attribute value
     */
    const VEHICLE_VISIBLE_IN_CATALOG_ONLY = 'Catalog Only';

    /**
     * @var string[]
     */
    protected $_attributeSets = [];

    /**
     * @var string[]
     */
    protected $_status = [];

    /**
     * @var string
     */
    protected $_processStarted = '';

    /**
     * @var null|Mage_Catalog_Model_Resource_Product_Attribute_Collection
     */
    protected $_productAttributes;

    protected $_processedProductIds = [];

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
     * @var Mage_Eav_Model_Entity_Setup
     */
    protected $_eavSetup;

    /**
     * @var array
     */
    protected $_websites = [];

    /**
     * @var array
     */
    protected $_categories = [];

    /**
     * @var array
     */
    protected $_importedVins = [];

    /**
     * @var array
     */
    protected $_deletedVins = [];

    /**
     * @var array
     */
    protected $_deletedIds = [];

    /**
     * @var array
     */
    protected $_attributeCodeOptions = [];

    /**
     * @var null|Mage_Core_Model_Resource
     */
    protected $_coreResource;

    /**
     * Delete products.
     *
     * @param [] $data
     * @return string[]
     * @throws Mage_Core_Exception
     */
    public function deleteProducts($data)
    {
        $this->_processStarted = 'delete';

        foreach ($data as $key => $value) {
            $product = Mage::helper('catalog/product')->getProduct($value['vin'], 0, 'sku');

            if (empty($product->getSku())) {
                $this->_status[$this->_processStarted]['error']['vin'][$value['vin']] = 'not found';
            } else {
                try {
                    $this->_deletedIds[] = $product->getId();
                    $product->delete();
                    $this->_status[$this->_processStarted]['success']['vin'][$value['vin']] = 'deleted';
                    $this->_deletedVins[] = $value['vin'];
                } catch (Exception $e) {
                    Mage::logException($e);
                    $this->_status[$this->_processStarted]['error']['vin'][$value['vin']] = $e->getMessage();
                }
            }
        }

        Mage::dispatchEvent(
            'peppermint_import_delete_products_after',
            ['vins' => $this->_deletedVins]
        );

        return $this->_status;
    }

    /**
     * Create or update products.
     *
     * @param [] $data
     * @return string[]
     */
    public function saveProducts($data)
    {
        $this->_processStarted = 'save';
        $productFailHelper = Mage::helper('peppermint_importer/product_fail');
        $allHelper = Mage::helper('rockar_all');

        try {
            if (is_array($data)) {
                foreach ($data as $productData) {
                    try {
                        $product = Mage::helper('catalog/product')->getProduct($productData['vin'], 0, 'sku');

                        $this->_processProductSaving($product, $productData['product_data']);
                        $this->_status[$this->_processStarted]['success']['vin'][$productData['vin']] = 'product saved';
                        $this->_importedVins[] = $productData['vin'];
                        // Delete, if exists, here from fail product table
                        // If product is successfully save or a new update came through for this paricular vin between cron jobs
                        $productFailHelper->syncWithProductFailTable($productData['vin'], null, true);

                        unset($product); // clean php memory
                    } catch (Exception $e) {
                        Mage::logException($e);
                        // Insert Data to product fail table if there an error during product saving
                        $productFailHelper->syncWithProductFailTable(
                            $productData['vin'],
                            $allHelper->jsonEncode($productData)
                        );

                        $this->_status[$this->_processStarted]['error']['vin'][] = $e->getMessage();
                    }
                }
            } else {
                $this->_status[$this->_processStarted]['error'] = 'No product data';
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_status[$this->_processStarted]['error'] = $e->getMessage();
        }

        unset($data); // clean php memory

        return $this->_status;
    }

    /**
     * Process product data.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param [] $data
     *
     * @return void
     * @throws Mage_Core_Exception
     */
    protected function _processProductSaving(Mage_Catalog_Model_Product &$product, array $data)
    {
        $this->_processProductAttributes($product, $data);
        $this->_processProductSystemAttributes($product, $data);
    }

    /**
     * Process product attributes.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $data
     * @return void
     * @throws Mage_Core_Exception
     */
    protected function _processProductAttributes(Mage_Catalog_Model_Product &$product, array $data)
    {
        if (!Mage::registry('product_import')) {
            Mage::register('product_import', true);
        }

        foreach ($data as $key => $value) {
            if ($value) {
                $attribute = $this->_getAttribute($key);
                // If attribute value is a label and need to fetch actual id
                if ($attribute && $attribute->getFrontendInput() == 'select') {
                    /**
                     * @todo add behaviour for when option id is not found due to not finding the option by value
                     * @todo load and map all options by value => option_id to avoid on the fly search in a loop
                     **/
                    $data[$key] = $this->_getAttributeOptionId($attribute, $value) ?: $this->_saveAttributeOption($attribute, $value);
                }
            }
        }

        $data['local_store_code'] = $data['bag_vehicle_location_code'] ?? '';

        // It needs special treatment, in the end it must have json format value
        if (!empty($data['standard_features'])) {
            $data['standard_features'] = json_encode($data['standard_features']);
        }

        if (!empty($data['technical_specification'])) {
            $data['technical_specification'] = json_encode($data['technical_specification']);
        }

        if (!empty($data['custom_options_prices'])) {
            $data['custom_options_prices'] = json_encode($data['custom_options_prices']);
        }

        if ($product->getId() && isset($data['media_gallery']['images'])) {
            // Get updated product images data | Mark old images for deletion and merging with new images data
            $data['media_gallery']['images'] = Mage::helper('peppermint_importer')->getUpdatedMediaGalleryImagesArray($product, $data);
        }

        $product->addData($data);
        unset($data);
        Mage::unregister('product_import');
    }

    /**
     * Get attribute option id from db, save in _attributeCodeOptions
     * to avoid multiple db calls for same attribute option id
     *
     * @param Mage_Eav_Model_Entity_Attribute $attribute
     * @param string $value
     * @return mixed|null
     */
    protected function _getAttributeOptionId(Mage_Eav_Model_Entity_Attribute $attribute, string $value)
    {
        if (!$this->_coreResource) {
            $this->_coreResource = Mage::getSingleton('core/resource');
        }

        if (!isset($this->_attributeCodeOptions[$attribute->getAttributeCode()])) {
            $this->_attributeCodeOptions[$attribute->getAttributeCode()] = [];
        }

        if (isset($this->_attributeCodeOptions[$attribute->getAttributeCode()][$value])) {
            return $this->_attributeCodeOptions[$attribute->getAttributeCode()][$value];
        }

        $collection = Mage::getResourceModel('eav/entity_attribute_option_collection')
            ->setAttributeFilter($attribute->getId());

        $collection->getSelect()
            ->join(
                ['eaov' => $this->_coreResource->getTableName('eav_attribute_option_value')],
                'main_table.option_id = eaov.option_id'
            )
            ->where('eaov.value = ?', $value)
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns([
                'option_id' => 'main_table.option_id',
            ])
            ->limit(1);

        if (!$collection->getSize()) {
            return null;
        }

        $this->_attributeCodeOptions[$attribute->getAttributeCode()][$value] = $collection
            ->getFirstItem()->getOptionId();

        return $this->_attributeCodeOptions[$attribute->getAttributeCode()][$value];
    }

    /**
     * Save attribute option that doesn't exist yet
     *
     * @param Mage_Eav_Model_Entity_Attribute $attribute
     * @param string $value
     *
     * @return int|null
     */
    protected function _saveAttributeOption($attribute, $value)
    {
        $this->_eavSetup->addAttributeOption(
            [
                'attribute_id' => $attribute->getAttributeId(),
                'value' => [
                    'option_' . trim(strtolower($value)) => [
                        Mage_Core_Model_App::ADMIN_STORE_ID => $value
                    ]
                ]
            ]
        );
        // need to refresh attribute collection with new attributes and options
        $this->_productAttributes = null;

        return $this->_getAttributeOptionId($attribute, $value);
    }

    /**
     * Get attribute.
     *
     * @param string $code
     *
     * @return Varien_Object
     */
    protected function _getAttribute($code)
    {
        return $this->_getProductAttributes()->getItemByColumnValue('attribute_code', $code);
    }

    /**
     * Get used product attributes.
     *
     * @return Mage_Catalog_Model_Resource_Product_Attribute_Collection
     */
    protected function _getProductAttributes()
    {
        if ($this->_productAttributes === null) {
            $this->_productAttributes = Mage::getResourceModel('catalog/product_attribute_collection')->clear()
                ->addFieldToFilter('attribute_code', ['in' => $this->_attributeCodes]);
        }

        return $this->_productAttributes;
    }

    /**
     * Process product system attributes.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param [] $data
     *
     * @return void
     */
    protected function _processProductSystemAttributes(Mage_Catalog_Model_Product &$product, array $data)
    {
        // Set store
        if (!empty($data['website'])) {
            $websiteId = $this->_getWebsiteId($data['website']);

            if ($websiteId) {
                unset($data['website']);
                $product->setWebsiteIds([$websiteId]);
            }
        }

        // Set attribute set
        if ($attributeSetId = $this->_getAttributeSet($data['attribute_set_id'])) {
            unset($data['attribute_set_id']);
            $product->setAttributeSetId($attributeSetId);
        }

        // Set stock data only at created product
        if (!$product->getId()) {
            $product->setStockData(Mage::helper('peppermint_importer')->productDefaultStockData());
        }

        // Set category id
        if (!empty($data['category_data'])) {
            $categoryData = $data['category_data'];

            if (isset($categoryData['root_category'], $categoryData['category'], $websiteId)) {
                $categoryId = $this->_getCategoryId(
                    $categoryData['root_category'],
                    $categoryData['category'],
                    $websiteId
                );

                if ($categoryId) {
                    $product->setCategoryIds([$categoryId]);
                }
            }
        }

        // set visible in attribute
        if (isset($data['visible_in']) && $data['visible_in'] === self::VEHICLE_VISIBLE_IN_CATALOG_ONLY) {
            $product->setData('visible_in', Rockar_YouDrive_Model_Product_Attribute_Source_Visiblein::CATALOG);
        }

        // Set customOptions
        if (!empty($data['custom_options'])) {
            if ($product->getId()) {
                foreach ($product->getOptions() as $option) {
                    $option->delete();
                }
            }
            $customOptionsData = $data['custom_options'];
            unset($data['custom_options']);
            $options = [];

            if ($customOptionsData) {
                $optionTypeSelected = Rockar_Catalog_Model_Catalog_Product_Option::OPTION_TYPE_SELECTED_CONFIGURATION;

                foreach ($customOptionsData as $customOptionValue) {
                    switch ($customOptionValue['view']) {
                        case 'Options':
                            $customOptionValuesOptions[] = $customOptionValue;
                            break;
                        case 'Line/Package':
                            $customOptionValuesLinePackage[] = $customOptionValue;
                            break;
                        case 'Extra Options':
                            $customOptionValuesExtraOptions[] = $customOptionValue;
                            break;
                    }
                }

                if (!empty($customOptionValuesOptions)) {
                    $options[] = [
                        'title' => 'Options',
                        'type' => $optionTypeSelected,
                        'is_require' => 0,
                        'sort_order' => 10,
                        'values' => $customOptionValuesOptions
                    ];
                }

                if (!empty($customOptionValuesLinePackage)) {
                    $options[] = [
                        'title' => 'Line/Packages',
                        'type' => $optionTypeSelected,
                        'is_require' => 0,
                        'sort_order' => 20,
                        'values' => $customOptionValuesLinePackage
                    ];
                }

                if (!empty($customOptionValuesExtraOptions)) {
                    $options[] = [
                        'title' => 'Extra Options',
                        'type' => $optionTypeSelected,
                        'is_require' => 0,
                        'sort_order' => 30,
                        'values' => $customOptionValuesExtraOptions
                    ];
                }
            }

            if (!empty($options)) {
                Mage::getSingleton('catalog/product_option')->unsetOptions();
                $product->setCanSaveCustomOptions(true)
                    ->setProductOptions($options);
            }
        }

        if (!$product->getTypeId()) {
            $product->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE);
        }

        $product->save();
        $this->_processedProductIds[] = $product->getId();
    }

    /**
     * Get attribute set id by name.
     *
     * @param string $name
     * @return integer
     */
    protected function _getAttributeSet($name)
    {
        if (!array_key_exists($name, $this->_attributeSets)) {
            $attributeSet = Mage::getModel('eav/entity_attribute_set')->getCollection()
                ->addFieldToFilter('attribute_set_name', ['eq' => $name])
                ->addFieldToFilter('entity_type_id', ['eq' => Mage::getSingleton('eav/config')->getEntityType(Mage_Catalog_Model_Product::ENTITY)->getId()])
                ->setPageSize(1)
                ->getFirstItem();
            $this->_attributeSets[$name] = $attributeSet->getId() ?: 0;
        }

        return $this->_attributeSets[$name];
    }

    /**
     * Callback method that will receive queue message and sending to be processed.
     *
     * @param string|array $msg
     *
     * @throws Mage_Core_Exception
     * @return void
     */
    public function processProducts($msg)
    {
        try {
            $productActions = is_array($msg)
                ? $msg
                : (Mage::helper('rockar_all')->jsonDecode($msg) ?: []);
        } catch (Exception $e) {
            Mage::logException($e);
        }
        Mage::register('peppermint_import', true);
        $this->_eavSetup = Mage::getModel('eav/entity_setup', 'core_setup');

        $appEmulation = Mage::getSingleton('core/app_emulation');
        $adminEnvironmentEmulation = $appEmulation->startEnvironmentEmulation(Mage_Core_Model_App::ADMIN_STORE_ID);

        foreach ($productActions as $action => $products) {
            switch ($action) {
                case 'delete':
                    if (!empty($products)) {
                        $status = $this->deleteProducts($products);
                    }
                    break;
                case 'update':
                case 'add':
                    if (!empty($products)) {
                        $status = $this->saveProducts($products);
                    }
                    break;
            }
        }

        $importedSkus = $this->_importedVins ?: null;

        // Aggregate simple products into configurables
        Mage::getModel('peppermint_importer/aggregator')->doAggregate(true, $importedSkus);

        Mage::unregister('peppermint_import');
        Mage::dispatchEvent(
            'peppermint_import_products_after',
            [
                'product_ids' => array_merge($this->_processedProductIds, $this->_getUpdatedConfigurableIds()),
                'deleted_product_ids' => $this->_deletedIds,
                'imported_simples' => $this->_processedProductIds
            ]
        );

        $appEmulation->stopEnvironmentEmulation($adminEnvironmentEmulation);
    }

    /**
     * Retrieve configurable product ids of imported simple products
     *
     * @return array
     */
    private function _getUpdatedConfigurableIds(): array
    {
        $configurablesIds = [];

        /** @var Mage_Catalog_Model_Resource_Product_Type_Configurable_Product_Collection $superLinkCollection */
        $superLinkCollection = Mage::getResourceModel('catalog/product_type_configurable_product_collection');
        $superLinkCollection->getSelect()
            ->reset()
            ->from($superLinkCollection->getTable('catalog/product_super_link'), ['parent_id'])
            ->where('product_id in (?)', $this->_processedProductIds)
            ->group('parent_id');

        foreach ($superLinkCollection as $item) {
            $configurablesIds[] = $item->getParentId();
        }

        return $configurablesIds;
    }

    /**
     * Get website id
     *
     * @param string $code
     * @return string|null
     */
    protected function _getWebsiteId($code)
    {
        if (!isset($this->_websites[$code])) {
            $this->_websites[$code] = Mage::getModel('core/website')->load($code, 'code')->getId();
        }

        return $this->_websites[$code];
    }

    /**
     * Returns child category Id of specified root category
     *
     * @param string $root
     * @param string $child
     * @param string $websiteId
     * @return string|null
     */
    protected function _getCategoryId($root, $child, $websiteId)
    {
        if (!isset($this->_categories[$root][$child])) {
            $rootCategoryId = Mage::getModel('core/store')->load($websiteId)
                ->getRootCategoryId();

            if ($rootCategoryId) {
                $parentCat = Mage::getModel('catalog/category')->getCollection()
                    ->addIdFilter($rootCategoryId)
                    ->setPageSize(1)
                    ->setCurPage(1)
                    ->getFirstItem();

                $childsCategory = $parentCat->getChildrenCategories();

                foreach ($childsCategory as $category) {
                    if ($category->getName() === $child) {
                         $this->_categories[$root][$child] = $category->getId();
                    }
                }
            }
        }

        return $this->_categories[$root][$child] ?? null;
    }
}

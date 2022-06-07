<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Importer
 * @author    Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Importer_Model_Aggregator extends Mage_Core_Model_Abstract
{
    /**
     * Configurable Product End Of Line Value
     * Configurable Product should not have end of line as yes
     */
    CONST CONFIG_PRODUCT_END_OF_LINE = 0;

    /**
     * A collection of attributes to clean from the cloned simple product.
     * @var string[]
     */
    protected $_attributesToClear = [
        'entity_id',
        'created_at',
        'updated_at',
        'vin_number',
        'stock_item',
        'short_vin_number'
    ];

    /**
     * A collection of configurable product attributes.
     * @var string[]
     * @todo extend the collection
     */
    protected $_configurableProductAttributes = ['color'];

    /**
     * @var null|Mage_Catalog_Model_Resource_Product_Attribute_Collection
     */
    protected $_productAttributes;

    /**
     * Constructor.
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->_productAttributes = Mage::getResourceModel('catalog/product_attribute_collection');
    }

    /**
     * Performs the aggregation of simple products into configurable ones.
     *
     * @param bool $isEmulated
     * @param array $importedSkus
     * @return void
     *
     * @throws Exception
     */
    public function doAggregate($isEmulated = false, $importedSkus = null)
    {
        if (!$isEmulated) {
            $appEmulation = Mage::getSingleton('core/app_emulation');
            $adminEnvironmentEmulation = $appEmulation->startEnvironmentEmulation(Mage_Core_Model_App::ADMIN_STORE_ID);
        }

        $youdriveConditoionOptionId = Mage::getResourceModel('catalog/product')->getAttribute('vehicle_condition')
            ->getSource()
            ->getOptionId(Peppermint_Catalog_Helper_Vehicle::CONDITION_TEST_DRIVE);

        $productModel = Mage::getModel('catalog/product');

        /** @var Rockar_Catalog_Model_Resource_Product_Collection $productCollection */
        $productCollection = $productModel->getCollection()
            ->addAttributeToSelect(['entity_id', 'group_key_short', 'vehicle_condition'])
            ->addAttributeToFilter('type_id', Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
            ->addAttributeToFilter(
                'vehicle_condition',
                ['neq' => $youdriveConditoionOptionId]
            )
            ->joinAttribute(
                'group_key_short',
                'catalog_product/group_key_short',
                'entity_id',
                null,
                'inner',
                Mage_Core_Model_App::ADMIN_STORE_ID
            );

        if ($importedSkus) {
            $productCollection->addAttributeToFilter('sku', ['in' => $importedSkus]);
        } else {
            /** @var Mage_Catalog_Model_Resource_Product_Type_Configurable_Product_Collection $superLinkCollection */
            $superLinkCollection = Mage::getResourceModel('catalog/product_type_configurable_product_collection');
            $superLinkCollection->getSelect()
                ->reset()
                ->from($superLinkCollection->getTable('catalog/product_super_link'), ['product_id']);

            $productCollection->addFieldToFilter(
                'entity_id',
                ['nin' => $superLinkCollection->getSelect()]
            );
        }

        $groupedProducts = [];

        foreach ($productCollection as $product) {
            if (!isset($groupedProducts[$product->getGroupKeyShort()])) {
                $groupedProducts[$product->getGroupKeyShort()] = [];
            }
            $groupedProducts[$product->getGroupKeyShort()][] = $product->getSku();
        }
        // Getting imported product ids and exisiting configurable products link from imported products
        $importedIds = $productCollection->getAllIds();
        $existingConfigIds = array_unique(Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild(
            $importedIds
        ));

        /** @var Mage_Catalog_Model_Resource_Product_Collection $configurableProductCollection */
        $configurableProductCollection = $productModel->getCollection()
            ->addAttributeToFilter('type_id', Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)
            ->addFieldToFilter('sku', ['in' => array_keys($groupedProducts)])
            ->load();

        $existingConfigurableProducts = [];

        foreach ($configurableProductCollection as $configurableProduct) {
            $existingConfigurableProducts[$configurableProduct->getSku()] = $configurableProduct;
        }

        $updatedConfigurableSkus = [];
        $newOrUpdatedConfigIds = [];

        foreach ($groupedProducts as $groupKey => $productSkus) {
            $configurableProduct = $existingConfigurableProducts[$groupKey] ?? null;

            if (!in_array($groupKey, $updatedConfigurableSkus)) {
                $configurableProduct = $this->_createConfigurableProductFromSimpleProduct(
                    $productSkus[0],
                    $groupKey,
                    $configurableProduct
                );
            }

            $updatedConfigurableSkus[] = $groupKey;
            $this->_assignSimpleProductsToConfigurable($configurableProduct, $productSkus);
            $newOrUpdatedConfigIds[] = $configurableProduct->getId();
        }

        // unlink old configurable products if group key change and delete empty configurable products
        $this->_unlinkConfigurableFromSimpleProduct(array_diff($existingConfigIds, $newOrUpdatedConfigIds), $importedIds);

        if (!$isEmulated) {
            $appEmulation->stopEnvironmentEmulation($adminEnvironmentEmulation);
        }
    }

    /**
     * Creates a configurable product out of the data from a simple product.
     *
     * @param string[] $simpleProductSku sku of the simple product to clone
     * @param string $newSku sku of the desired configurable product
     * @param Mage_Catalog_Model_Product $configurableProduct
     *
     * @return Mage_Catalog_Model_Product
     * @throws Exception
     */
    private function _createConfigurableProductFromSimpleProduct($simpleProductSku, $newSku, $configurableProduct = null)
    {
        $reindexUrlRewrite = false;

        Mage::log(sprintf('Create configurable product %s from simple %s', $newSku, $simpleProductSku));
        /** @var Mage_Catalog_Model_Product $simpleProduct */
        $simpleProduct = Mage::helper('catalog/product')->getProduct($simpleProductSku, Mage_Core_Model_App::ADMIN_STORE_ID, 'sku');

        $data = $simpleProduct->getData();

        foreach ($this->_attributesToClear as $column) {
            unset($data[$column]);
        }

        foreach ($data['media_gallery']['images'] as &$image) {
            unset($image['value_id'], $image['product_id']);
        }

        $data['type_id'] = Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE;
        $data['sku'] = $newSku;
        $data['visibility'] = Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH;
        $data['end_of_line'] = self::CONFIG_PRODUCT_END_OF_LINE;
        $importHelper = Mage::helper('peppermint_importer');

        if (!$configurableProduct) {
            $data['stock_data'] = $importHelper->productDefaultStockData();
        }

        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::getModel('catalog/product');

        if ($configurableProduct) {
            $product = $product->load($configurableProduct->getId());

            // Get updated product images data | Mark old images for deletion and merging with new images data
            $data['media_gallery']['images'] = $importHelper->getUpdatedMediaGalleryImagesArray($product, $data);
        }

        //clear globally set options
        Mage::getSingleton('catalog/product_option')->unsetOptions();

        if ($product->getId()) {
            foreach ($product->getOptions() as $option) {
                $option->delete();
            }
        } else {
            // Product does not exist yet, schedule URL rewrites reindex
            $reindexUrlRewrite = true;
        }

        $product->addData($data)
            ->setCategoryIds($simpleProduct->getCategoryIds())
            ->setStoreId($simpleProduct->getStoreId())
            ->setWebsiteIds($simpleProduct->getWebsiteIds());

        $options = $this->getProductOptionsDataCopy($simpleProduct);

        $product->setCanSaveCustomOptions(true)
            ->setProductOptions($options);

        if (!$product->getId()) {
            $configurableAttributesData = $product->getTypeInstance()
                ->setUsedProductAttributeIds($this->_getConfigurableProductAttributeIds())
                ->getConfigurableAttributesAsArray();

            $product->setCanSaveConfigurableAttributes(true)
                ->setConfigurableAttributesData($configurableAttributesData);
        }

        $product->save();

        if ($reindexUrlRewrite) {
            // Ensure URL rewrite rule is created for the newly created configurable
            // product to make it accessible in car finder
            $event = Mage::getSingleton('index/indexer')->logEvent(
                $product,
                $product->getResource()->getType(),
                Mage_Index_Model_Event::TYPE_SAVE,
                false
            );

            Mage::getSingleton('index/indexer')->getProcessByCode('catalog_url')
                ->setMode(Mage_Index_Model_Process::MODE_REAL_TIME)
                ->processEvent($event);
        }

        return $product;
    }

    /**
     * Performs the association of simple products to a configurable product.
     * @param Mage_Catalog_Model_Product $configurableProduct
     * @param string[] $simpleProductSkus an array of simple products skus to associate
     * @return void
     */
    private function _assignSimpleProductsToConfigurable(Mage_Catalog_Model_Product $configurableProduct, $simpleProductSkus)
    {
        $productIds = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToFilter('sku', ['in' => $simpleProductSkus])
            ->getAllIds();

        $existingProductsIds = array_values(Mage::getModel('catalog/product_type_configurable')->getChildrenIds($configurableProduct->getId())[0]);

        Mage::getResourceSingleton('catalog/product_type_configurable')
            ->saveProducts($configurableProduct, array_unique(array_merge($productIds, $existingProductsIds)));
    }

    /**
     * Performs disassociation of simple products and outdated configurable products
     * Deleting empty configurable products
     *
     * @param array $oldConfigProductIds
     * @param array $importedIds
     * @return void
     */
    protected function _unlinkConfigurableFromSimpleProduct($oldConfigProductIds, $importedIds)
    {
        if ($oldConfigProductIds) {
            try {
                $configProductCollection = Mage::getResourceModel('catalog/product_collection')
                    ->addAttributeToSelect('entity_id')
                    ->addAttributeToFilter('type_id', Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)
                    ->addAttributeToFilter('entity_id', ['in' => $oldConfigProductIds]);

                $configProductResourceModel = Mage::getResourceSingleton('catalog/product_type_configurable');
                $productToDelete = [];

                foreach ($configProductCollection as $product) {
                    $existingProductsIds = array_values(
                        $configProductResourceModel->getChildrenIds($product->getId())[0] ?? []
                    );

                    $currentSimpleIds = array_diff($existingProductsIds, $importedIds);

                    // If diff is empty array there is only one simple under this configurable | we can then delete it since there
                    // No more simple products under it
                    if (!$currentSimpleIds) {
                        $productToDelete[] = $product->getId();
                    } elseif ($existingProductsIds) {
                        // else save existing configurable product link without newly imported product
                        // since it got updated with a new group key
                        $configProductResourceModel->saveProducts(
                            $product,
                            array_filter($existingProductsIds, function ($productId) use ($importedIds) {
                                return !in_array($productId, $importedIds);
                            })
                        );
                    }
                }
                // Delete empty configurable products
                $this->_deleteProducts($productToDelete);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }


    /**
     * Delete product from catalog_product_entity table
     *
     * @param array $ids
     * @return void
     * @throws Exception
     */
    protected function _deleteProducts($ids)
    {
        if ($ids && is_array($ids)) {
            $resource = Mage::getSingleton('core/resource');
            $write = $resource->getConnection('core_write');

            $write->delete(
                $resource->getTableName('catalog/product'),
                ['entity_id IN(?)' => $ids]
            );
        }
    }

    /**
     * Get base configurable product attribute ids.
     * @return string[]
     */
    protected function _getConfigurableProductAttributeIds()
    {
        return array_map(
            function ($attributeName) {
                return $this->_productAttributes->getItemByColumnValue('attribute_code', $attributeName)
                    ->getId();
            },
            $this->_configurableProductAttributes
        );
    }

    /**
     * Get array representing simple products options
     *
     * Overridden to add highligted & cosy_url
     *
     * @param $simpleProduct
     * @return array
     */
    private function getProductOptionsDataCopy($simpleProduct)
    {
        $simpleProductOptions = $simpleProduct->getOptions();
        $options = [];

        foreach ($simpleProductOptions as $option) {
            $values = [];

            foreach ($option->getValues() as $value) {
                $values[] = [
                    'title' => $value->getTitle(),
                    'price' => $value->getPrice(),
                    'price_type' => $value->getPriceType(),
                    'sku' => $value->getSku(),
                    'sort_order' => $value->getSortOrder(),
                    'view' => $option->getData('title'),
                    'highligted' => $value->getHighligted(),
                    'cosy_url' => $value->getCosyUrl()
                ];
            }

            $options[] = [
                'type' => $option->getData('type'),
                'title' => $option->getData('title'),
                'is_require' => $option->getData('is_require'),
                'sort_order' => $option->getData('sort_order'),
                'values' => $values
            ];
        }

        return $options;
    }
}

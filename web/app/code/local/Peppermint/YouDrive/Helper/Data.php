<?php
/**
 * @category  Peppermint
 * @package   Peppermint_YouDrive
 * @author    Taras Kapushchak <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

/**
 * Class Peppermint_YouDrive_Helper_Data
 */
class Peppermint_YouDrive_Helper_Data extends Rockar_YouDrive_Helper_Data
{
    const XML_PATH_TEST_DRIVE_TITLE = 'rockar_youdrive/youdrive_general/test_drive_title';
    const XML_PATH_EMAIL_INFO_TEXT = 'rockar_youdrive/youdrive_general/email_change_info';

    /**
     * Attribute that maps compounds to local stores
     *
     * @var string
     */
    protected $_compoundMappingAttribute = 'associated_compound_dealer';

    /**
     * Get car model attribute name
     *
     * @param $modelIds
     * @return array|mixed
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getCarsByModelIds($modelIds)
    {
        if (isset($this->_carsByModelIds[implode('', $modelIds)])) {
            return $this->_carsByModelIds[implode('', $modelIds)];
        }

        $helper = Mage::helper('rockar_catalog/vehicle');
        $modelAttributeName = Mage::helper('rockar_all')->getModelAttribute();
        $configurableProducts = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect(['name', $modelAttributeName, 'visible_in'])
            ->addAttributeToFilter('type_id', 'simple')
            ->addAttributeToFilter('visible_in', [
                'in' => [
                    Rockar_YouDrive_Model_Product_Attribute_Source_Visiblein::YOUDRIVE,
                    Rockar_YouDrive_Model_Product_Attribute_Source_Visiblein::CATALOG_AND_YOUDRIVE
                ]
            ])
            ->addAttributeToFilter($modelAttributeName, ['in' => $modelIds])
            ->setStore(Mage::app()->getStore())
            ->addStoreFilter();

        $simpleProducts = $this->getAssociatedSimpleProducts($configurableProducts);

        $modelArray = [];

        if ($simpleProducts->getSize()) {
            foreach ($simpleProducts as $product) {
                // 1 product can have multiple youdrive vehicles with different ids
                $youdriveVehicles = explode(',', $product->getData('youdrive_vehicles'));

                foreach ($youdriveVehicles as $youdriveVehicle) {
                    $data = explode(':', $youdriveVehicle);

                    $modelArray[] = [
                        'id' => $product->getId(),
                        'youdriveId' => isset($data[0]) ? $data[0] : 0,
                        'options' => $this->getGroupedCarOptions($product->load($product->getId())),
                        'image' => (string) Mage::helper('peppermint_catalog/images')->getSmallImage($product),
                        'title' => ($helper->getTitle($product)) ? $helper->getTitle($product) : $product->getName(),
                        'subtitle' => ($helper->getSubTitle($product)) ? $helper->getSubTitle($product) : '',
                        'model' => $product->getData($modelAttributeName),
                        'selected' => false,
                        'assignedTo' => isset($data[1]) ? $data[1] : 0,
                        'productClass' => $product->getProductClass()
                    ];
                }
            }
        }

        $this->_carsByModelIds[implode('', $modelIds)] = $modelArray;

        return $modelArray;
    }

    /**
     * Get youdrive configurables products
     *
     * @param null $modelId
     * @param bool $isSimple
     *
     * @return mixed
     */
    public function getConfigurablesCollection($modelId = null, $isSimple = false)
    {
        /** @var Rockar_Localstores_Helper_Data $localStoresHelper */
        $localStoresHelper = Mage::helper('rockar_localstores');

        $resource = Mage::getSingleton('core/resource');
        $modelAttributeName = Mage::helper('rockar_all')->getModelAttribute();
        $typeId = $isSimple ? Mage_Catalog_Model_Product_Type::TYPE_SIMPLE : Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE;
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect(['name', $modelAttributeName, 'visible_in'])
            ->addAttributeToFilter('type_id', $typeId)
            ->addAttributeToFilter([
                [
                    'attribute' => 'visible_in',
                    'in' => [
                        Rockar_YouDrive_Model_Product_Attribute_Source_Visiblein::YOUDRIVE,
                        Rockar_YouDrive_Model_Product_Attribute_Source_Visiblein::CATALOG_AND_YOUDRIVE
                    ]
                ]
            ], '', 'left')
            ->setStore(Mage::app()->getStore())
            ->addStoreFilter();

        // Has dummy 0 element to prevent empty IN mysql statement resulting in error
        $localStoreCodes = [0];

        // Get stores that are:
        // - enabled
        // - enabled for YD
        // - has some timeslot
        $localStores = $localStoresHelper->getStoreList(Rockar_Localstores_Helper_Data::STORE_AVAILABILITY_YOUDRIVE);
        $youDriveStoreCodes = $localStores->getColumnValues($this->getLocalStoreMappingAttribute());

        // get compound codes associated to the local stores
        $compoundCodes = array_filter(
            $localStores->getColumnValues($this->getCompoundMappingAttribute()),
            function ($item) {
                return $item !== null;
            }
        );

        $localStoreCodes = array_merge($localStoreCodes, $youDriveStoreCodes, $compoundCodes);
        $codesString = sprintf('\'%s\'', implode('\',\'', $localStoreCodes));

        $collection->getSelect()
            ->join(
                ['y' => $resource->getTableName('rockar_youdrive/vehicle')],
                sprintf(
                    'y.parent_id = e.entity_id AND y.is_active = %s AND y.assigned_to IN(%s)',
                    Rockar_Localstores_Model_Resource_Stores_Collection::STATUS_ENABLED,
                    $codesString
                ),
                ['youdriveId' => 'y.id']
            )
            ->group('e.entity_id');

        return $collection;
    }

    /**
     * Get associated simple products
     *
     * @param mixed $configurableProducts
     * @param boolean $checkByModel
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getAssociatedSimpleProducts($configurableProducts, $checkByModel = false)
    {
        $resource = Mage::getSingleton('core/resource');
        $modelAttributeName = Mage::helper('rockar_all')->getModelAttribute();
        $modelValues = [];

        $associatedSimpleProductIds = [];

        foreach ($configurableProducts as $configurableProduct) {
            if ($configurableProduct->isConfigurable()) {
                $simpleProductIds = $configurableProduct->getTypeInstance()->getUsedProductIds();
                $modelValues[$configurableProduct->getData($modelAttributeName)] = 1;

                foreach ($simpleProductIds as $id) {
                    $associatedSimpleProductIds[] = $id;
                }
            } else {
                $simpleProductIds = $configurableProduct->getId();
                $modelValues[$configurableProduct->getData($modelAttributeName)] = 1;
                $associatedSimpleProductIds[] = $simpleProductIds;
            }
        }

        $collection = Mage::getResourceModel('catalog/product_collection')->addAttributeToFilter([
                [
                    'attribute' => 'visible_in',
                    'in' => [
                        Rockar_YouDrive_Model_Product_Attribute_Source_Visiblein::YOUDRIVE,
                        Rockar_YouDrive_Model_Product_Attribute_Source_Visiblein::CATALOG_AND_YOUDRIVE
                    ]
                ]
            ], '', 'left')
            ->setStore(Mage::app()->getStore())
            ->addStoreFilter();

        if ($checkByModel) {
            $collection->addAttributeToFilter($modelAttributeName, ['in' => array_keys($modelValues)]);
        } elseif (!empty($associatedSimpleProductIds)) {
            $collection->addAttributeToFilter('entity_id', ['in' => $associatedSimpleProductIds]);
        }

        $storeCodes = [0];
        $localStores = Mage::getResourceModel('rockar_localstores/stores_collection')->getYouDriveStore();

        if ($localStores) {
            $storeCodes = array_merge($storeCodes,
                $localStores->getColumnValues($this->getLocalStoreMappingAttribute()));
        }

        // get compound codes associated to the local stores
        $compoundCodes = array_filter(
            $localStores->getColumnValues($this->getCompoundMappingAttribute()),
            function ($item) {
                return $item !== null;
            }
        );

        $storeCodes = array_merge($storeCodes, $compoundCodes);
        $codesString = sprintf('\'%s\'', implode('\',\'', $storeCodes));

        // join youdrive vehicles table
        // each product can have multiple different youdrive vehicles, assigned to different stores
        $collection->getSelect()
            ->join(
                ['y' => $resource->getTableName('rockar_youdrive/vehicle')],
                'y.product_id = e.entity_id AND y.is_active = 1 AND y.assigned_to IN (' . $codesString . ')',
                ['youdrive_vehicles' => 'GROUP_CONCAT(CONCAT(y.id, \':\', y.assigned_to))'] // e.g. youdrive_vehicles = 123:000abc,456:000def
            )
            ->order('y.position')
            ->group('e.entity_id');

        return $collection;
    }

    /**
     * Rewrite parent function to add vehicles
     * that are available for request
     *
     * {@inheritDoc}
     */
    public function getStoresByModelIds($modelIds)
    {
        if (!is_array($modelIds)) {
            $modelIds = [$modelIds];
        }

        if (isset($this->_storesByModelIds[implode('', $modelIds)])) {
            return $this->_storesByModelIds[implode('', $modelIds)];
        }

        /** @var Peppermint_Localstores_Helper_Data $localStoresHelper */
        $localStoresHelper = Mage::helper('peppermint_localstores/data');
        /** @var Rockar_All_Helper_Data $allHelper */
        $allHelper = Mage::helper('rockar_all');
        $stores = $localStoresHelper->getStoreList($localStoresHelper::STORE_AVAILABILITY_YOUDRIVE);

        /**
         * Add youdrive vehicles corresponding products data for each store
         */
        $products = $this->getCarsByModelIds($modelIds);
        foreach ($stores as $key => $store) {
            $store->setVehicles([]);

            if ($store->getProductIds() ||
                ($store->getEnableYoudriveRequest() && $store->getAssociatedCompoundDealer())) {
                /** Filter out products that are assigned to corresponding store via youdrive vehicles
                 *  or can be requested from associated compound,
                 *  also make sure that only stores corresponding to the selected model brands are selected
                 */
                $vehicles = array_filter($products, function ($item) use ($store) {
                    return (
                        $item['assignedTo'] &&
                        in_array($item['productClass'], explode(',', $store->getAssociatedBrand()), true)  &&
                        ($item['assignedTo'] === $store->getCode() ||
                            ($item['assignedTo'] === $store->getAssociatedCompoundDealer() &&
                                $store->getEnableYoudriveRequest()))
                    );
                });

                // If the store has no vehicles then remove that store
                if (!$vehicles) {
                    $stores->removeItemByKey($key);
                } else {
                    $store->setVehicles(array_values($vehicles));
                }
            } else {
                // Remove stores that have no products/vehicles assigned
                $stores->removeItemByKey($key);
            }
        }

        $attribute = Mage::getSingleton('eav/config')
            ->getAttribute(Mage_Catalog_Model_Product::ENTITY, $allHelper->getModelAttribute());

        /**
         * Check in subselect if products available in store with given parent product models
         */
        $availabilitySelect = Mage::getSingleton('core/resource')->getConnection('core_read')->select()
            ->from(['model' => $attribute->getBackendTable()], [''])
            ->join(
                ['link' => $stores->getResource()->getTable('catalog/product_super_link')],
                'model.entity_id = link.parent_id',
                ['link.product_id']
            )
            ->join(
                ['availability' => $stores->getResource()->getTable('rockar_youdrive/vehicle')],
                'link.product_id = availability.product_id AND availability.is_active = 1',
                ['availability.assigned_to']
            )
            ->where("model.value IN(?) AND model.attribute_id = '{$attribute->getId()}' AND availability.assigned_to = main_table.code", $modelIds);

        $stores->getSelect()->where('EXISTS(' . (string)$availabilitySelect . ')');
        $storesArray = $localStoresHelper->formatStoreData($stores, $localStoresHelper::STORE_AVAILABILITY_YOUDRIVE);
        $this->_storesByModelIds[implode('', $modelIds)] = $storesArray;

        return $storesArray;
    }

    /**
     * Get the compound mapping attribute
     *
     * @return string
     */
    public function getCompoundMappingAttribute()
    {
        return $this->_compoundMappingAttribute;
    }

    /**
     * Get Test Drive Title from config
     *
     * @return string|null
     */
    public function getTestDriveTitle()
    {
        return Mage::getStoreConfig(self::XML_PATH_TEST_DRIVE_TITLE);
    }

    /**
     * Get email change text from config
     *
     * @return string|null
     */
    public function getEmailInfoText()
    {
        return Mage::getStoreConfig(self::XML_PATH_EMAIL_INFO_TEXT);
    }
}

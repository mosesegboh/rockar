<?php
/**
 * @category  Peppermint
 * @package   Peppermint_UcpConfig
 * @author    Kalvis Ostrovskis <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_UcpConfig_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * API endpoint path
     */
    protected const API_ENDPOINT_PATH = 'rockar_all/ucpconfig/endpoint_url';

    /**
     * Attribute mapping
     */
    protected const ATTRIBUTES = [
        'modelShortDescription' => 'variant',
        'interiorCode' => 'interior',
        'exteriorCode' => 'exterior',
        'bodyStyle' => 'bodystyle'
    ];

    /**
     * Option to attribute mapping
     */
    protected const OPTIONS = [
        'FELGE' => 'wheel'
    ];

    /**
     * Param for configuration id
     */
    public const CONFIG_ID = 'configID';

    /**
     * @var string
     */
    protected $url = '';

    /**
     * @var string
     */
    protected $modelMatrixAttribute = '';

    /**
     * @var array
     */
    protected $appliedFilters = [];

    /**
     * @var null|string
     */
    protected $adminStoreId;

    /**
     * @var null|Peppermint_Setup_Resource_Db_Pdo_Mysql_Adapter
     */
    protected $readConnection;

    /**
     * @var string
     */
    protected $apiEndpoint = '';

    /**
     * @param string $configId
     * @return array
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getRedirect(string $configId): array
    {
        $this->initValues();
        $result = [
            'url' => $this->url,
            'query' => [
                'step' => 'carFilter',
                'ucpConfigSkip' => 'true'
            ]
        ];

        if (!$this->getApiEndpoint()) {
            return $result;
        }

        $configValues = $this->getConfigValues($configId);

        if (!isset($configValues['modelCode']) || empty($configValues['modelCode'])) {
            return $result;
        }

        $collection = Mage::getResourceModel('catalog/category_collection')
            ->addFieldToFilter('url_key', $this->url)
            ->addFieldToFilter('path', ['like' => "1/{$this->getCurrentStore()->getRootCategoryId()}/%"])
            ->getFirstItem()
            ->getProductCollection();

        $collection->addAttributeToFilter('model_code_value', $configValues['modelCode'])
            ->setStoreId($this->getCurrentStore()->getId());

        if (!$collection->getSize()) {
            return $result;
        }

        // Cloned collection will be used in case of finance fallback
        $collectionClone = clone $collection;

        $financeHelper = Mage::helper('financing_options');
        //Reset finances to default
        $financeHelper->resetFinanceData();

        $layer = Mage::getModel('catalog/layer');
        $layer->prepareProductCollection($collection);

        $query = ['step' => 'carFilter'];

        if (!$modelMatrixCarousel = $collection->getFirstItem()->getData($this->modelMatrixAttribute)) {
            //Fallback to default pay in full payment
            $collection = $collectionClone;
            $defaultPayInFull = $financeHelper->getDefaultPayInFullPayment();
            $financeHelper->setFinanceData(['method' => $defaultPayInFull[0]['group_id']]);
            $query['method'] = $defaultPayInFull[0]['group_id'];
            $layer->prepareProductCollection($collection);
            $modelMatrixCarousel = $collection->getFirstItem()->getData($this->modelMatrixAttribute);
        }

        if (!$modelMatrixCarousel) {
            return $result;
        }

        $query[$this->modelMatrixAttribute] = [$modelMatrixCarousel];

        $this->applyFiltersToCollection($collection, $configValues);

        // Build query based on applied filters
        foreach ($this->appliedFilters as $attributeCode => $optionValue) {
            $query[$attributeCode] = [$optionValue];
        }

        return ['url' => $this->url, 'query' => $query];
    }

    /**
     * Get configuration values from API
     *
     * @param string $configId
     * @return array
     */
    protected function getConfigValues(string $configId): array
    {
        return $this->_doGetRequest(sprintf('%s%s', $this->getApiEndpoint(), $configId));
    }

    /**
     * Simple Get request on a specified URL with a provided payload
     *
     * @param string $url
     * @return array
     */
    protected function _doGetRequest(string $url): array
    {
        $curl = new Varien_Http_Adapter_Curl();
        $curl->setConfig(['timeout' => 30])
            ->write(
                Zend_Http_Client::GET,
                $url,
                '1.1',
                [
                    'Accept: application/json',
                    'Content-Type: application/json'
                ],
                ''
            );
        $returnData = Zend_Http_Response::extractBody($curl->read());
        $curl->close();

        return $this->_decodeResponse($returnData);
    }

    /**
     * Error handling for API
     *
     * @param string $data
     * @return array
     */
    protected function _decodeResponse(string $data): array
    {
        $jsonData = json_decode($data, true);

        if (
            strlen($data) === 0
            || ($jsonData === null && json_last_error() !== JSON_ERROR_NONE)
            || $jsonData->etReturn->error
            || ($jsonData->etReturn->messages && count($jsonData->etReturn->messages))
        ) {
            Mage::log(
                'Web service request has failed with response: ' . var_export($data, true),
                Zend_Log::ERR
            );

            return [];
        }

        return $jsonData;
    }

    /**
     * Apply all applicable filters to collection
     *
     * @param Rockar_Catalog_Model_Resource_Product_Collection $collection
     * @param array $configValues
     */
    protected function applyFiltersToCollection(Rockar_Catalog_Model_Resource_Product_Collection $collection, array $configValues)
    {
        foreach (self::ATTRIBUTES as $configAttribute => $dspAttribute) {
            if (isset($configValues[$configAttribute]) && !empty($configValues[$configAttribute])) {
                $filterData = $this->getFilterData($dspAttribute, $configValues[$configAttribute]);

                if (!$this->validateFilterData($filterData)) {
                    continue;
                }

                $collection = $this->applyFilterToCollection(
                    $filterData['attribute'],
                    $filterData['optionId'],
                    $collection
                );
            }
        }

        foreach ($configValues['options'] as $option) {
            if (!isset($option['featureGroup'])
                || empty($option['featureGroup'])
                || !array_key_exists($option['featureGroup'], self::OPTIONS)
            ) {
                continue;
            }

            $filterData = $this->getFilterData(self::OPTIONS[$option['featureGroup']], $option['optionCode']);

            if (!$this->validateFilterData($filterData)) {
                continue;
            }

            $collection = $this->applyFilterToCollection(
                $filterData['attribute'],
                $filterData['optionId'],
                $collection
            );
        }
    }

    /**
     * Validate filter data
     *
     * @param array $filterData
     * @return bool
     */
    protected function validateFilterData(array $filterData): bool
    {
        if (!isset($filterData['attribute'])
            || empty($filterData['attribute'])
            || !isset($filterData['optionId'])
            || empty($filterData['optionId'])
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @param string $value
     * @param Rockar_Catalog_Model_Resource_Product_Collection $collection
     * @return Rockar_Catalog_Model_Resource_Product_Collection
     */
    protected function applyFilterToCollection(Mage_Catalog_Model_Resource_Eav_Attribute $attribute, string $value, Rockar_Catalog_Model_Resource_Product_Collection $collection): Rockar_Catalog_Model_Resource_Product_Collection
    {
        // Cloned collection will be used in case filter isn't applicable
        $collectionClone = clone $collection;
        $connection = $this->getReadConnection();
        $tableAlias = $attribute->getAttributeCode() . '_idx';
        $conditions = [
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
            $connection->quoteInto("{$tableAlias}.store_id = ?", $collection->getStoreId()),
            $connection->quoteInto("{$tableAlias}.value = ?", $value)
        ];

        $collection->getSelect()->join(
            [$tableAlias => $connection->getTableName('catalog_product_index_eav')],
            implode(' AND ', $conditions),
            []
        );

        if (!$collection->getConnection()->fetchOne($collection->getSelectCountSql())) {
            return $collectionClone;
        }

        $this->appliedFilters[$attribute->getAttributeCode()] = $value;

        return $collection;
    }

    /**
     * Get read connection
     *
     * @return Magento_Db_Adapter_Pdo_Mysql
     */
    protected function getReadConnection(): Magento_Db_Adapter_Pdo_Mysql
    {
        if (!$this->readConnection) {
            $this->readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
        }

        return $this->readConnection;
    }

    /**
     * @return Mage_Core_Model_Store
     * @throws Mage_Core_Model_Store_Exception
     */
    protected function getCurrentStore(): Mage_Core_Model_Store
    {
        return Mage::app()->getStore();
    }

    /**
     * Get attribute and get option id from admin store based on attribute code and value
     *
     * @param string $attributeCode
     * @param string $value
     * @return array
     */
    protected function getFilterData(string $attributeCode, string $value): array
    {
        $attribute = Mage::getModel('eav/config')->getAttribute(
            Mage_Catalog_Model_Product::ENTITY,
            $attributeCode
        );

        if (!$attribute->getId() || !$attribute->usesSource()) {
            return [];
        }

        return [
            'attribute' => $attribute,
            'optionId' => $attribute->setStoreId($this->getAdminStoreId())->getSource()->getOptionId($value)
        ];
    }

    /**
     * Get Admin Store ID
     *
     * @return string
     */
    protected function getAdminStoreId(): string
    {
        if (!$this->adminStoreId) {
            $this->adminStoreId = Mage::getModel('core/store')->load('admin')->getId();
        }

        return $this->adminStoreId;
    }

    /**
     * Init brand specific values
     *
     * @throws Mage_Core_Model_Store_Exception
     */
    protected function initValues()
    {
        switch (str_replace('_store_view', '', $this->getCurrentStore()->getCode())) {
            case Peppermint_Catalog_Helper_Vehicle::BRAND_MINI_FULL:
                $this->modelMatrixAttribute = 'min_model_matrix_carousel';
                $this->url = 'car-finder';
                break;
            case Peppermint_Catalog_Helper_Vehicle::BRAND_MOTORRAD_FULL:
                $this->modelMatrixAttribute = 'mot_model_matrix_carousel';
                $this->url = 'bike-finder';
                break;
            default:
                $this->modelMatrixAttribute = 'bmw_model_matrix_carousel';
                $this->url = 'car-finder';
                break;
        }
    }

    /**
     * Get UcpConfig API Endpoint URL
     *
     * @return string
     */
    public function getApiEndpoint(): string
    {
        if (!$this->apiEndpoint) {
            $this->apiEndpoint = Mage::getStoreConfig(self::API_ENDPOINT_PATH) ?? '';
        }

        return $this->apiEndpoint;
    }
}

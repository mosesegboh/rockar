<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Jiraphong Witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_FinancingOptions_Model_Api_Map extends Rockar_FinancingOptions_Model_Api_Map
{
    /**
     * Select Attribute OptionIds
     *
     * @var array
     */
    protected $_attributeOptionIds = [];

    /**
     * vehicle condition attribute code
     */
    CONST VEHICLE_CON_ATTRIBUTE_CODE = 'vehicle_condition';

    /**
     * Map Products with Finance Data and save in DB
     *
     * @param array $data
     *
     * @return $this
     */
    protected function _mapProductsByCapIdAndVehicleCondition($data)
    {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $insertData = [];
        $data = $data['items'];
        $productsWithCustomData = $this->_getProductsWithCustomData();

        foreach ($data as $item) {
            $lookUpValue = $item['capid'] . '-' . $item['vehicle_type'];

            if (!isset($this->_capIdProductIdMapping[$lookUpValue])) {
                $productIds = $this->_loadProductByCapIdAndVehicleCondition(
                    $item['capid'],
                    $item['vehicle_type'],
                    $productsWithCustomData
                );
                $this->_capIdProductIdMapping[$lookUpValue] = $productIds;
            }

            if (isset($this->_capIdProductIdMapping[$lookUpValue])
                && !empty($this->_capIdProductIdMapping[$lookUpValue])
            ) {
                foreach ($this->_capIdProductIdMapping[$lookUpValue] as $productId) {
                    $insertData[] = [
                        'data_id' => $item['data_id'],
                        'product_id' => $productId
                    ];
                }
            }
        }

        $chunkData = array_chunk($insertData, self::CHUNK_SIZE);

        foreach ($chunkData as $insertData) {
            $write->insertMultiple('rockar_financing_data_products', $insertData);
        }

        return $this;
    }

    /**
     * Return product IDs by CAP_CODE and vehicle_condition attribute
     *
     * @param int $capId
     * @param string $vehicleType
     * @param array $productsWithCustomData
     *
     * @return array
     */
    protected function _loadProductByCapIdAndVehicleCondition($capId = 0, $vehicleType, $productsWithCustomData)
    {
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->addAttributeToFilter('cap_code', $capId);
        $attributeOptionId = $this->_getSelectAttributeOptionId(self::VEHICLE_CON_ATTRIBUTE_CODE, $vehicleType);

        if ($attributeOptionId) {
            $collection->addAttributeToFilter(self::VEHICLE_CON_ATTRIBUTE_CODE, $attributeOptionId);
        }

        if ($productsWithCustomData) {
            $collection->addAttributeToFilter('entity_id', ['nin' => $productsWithCustomData]);
        }

        return $collection->getColumnValues('entity_id');
    }

    /**
     * {@inheritDoc}
     */
    protected function _mapProducts($data)
    {
        return Mage::helper('peppermint_financingoptions/config')->getMappingByVehicleConditionConfig()
            ? $this->_mapProductsByCapIdAndVehicleCondition($data)
            : parent::_mapProducts($data);
    }

    /**
     * Get select attribute option Id
     *
     * @param string $attributeCode
     * @param string $value
     *
     * @return string|null
     */
    protected function _getSelectAttributeOptionId($attributeCode, $value)
    {
        $key = $attributeCode . $value;

        if (!isset($this->_attributeOptionIds[$key])) {
            $this->_attributeOptionIds[$key] = Mage::getResourceModel('catalog/product_attribute_collection')
                ->addFieldToFilter('attribute_code', $attributeCode)
                ->getFirstItem()
                ->getSource()
                ->getOptionId($value);
        }

        return $this->_attributeOptionIds[$key];
    }
}

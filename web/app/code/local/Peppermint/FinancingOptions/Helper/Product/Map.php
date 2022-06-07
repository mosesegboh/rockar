<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

/**
 * Class Peppermint_FinancingOptions_Helper_Product_Map
 */
class Peppermint_FinancingOptions_Helper_Product_Map extends Mage_Core_Helper_Abstract
{
    const CHUNK_SIZE = 10000;

    /**
     * vehicle condition attribute code
     */
    const VEHICLE_CON_ATTRIBUTE_CODE = 'vehicle_condition';

    /**
     * Maps finances to products by product ids
     *
     * @param array $productIds
     * @return void
     * @throws Exception
     */
    public function mapFinancesToProducts($productIds): void
    {
        $insertData = Mage::helper('peppermint_financingoptions/config')->getMappingByVehicleConditionConfig()
            ? $this->_mapFinancesToProductsWithCondition($productIds)
            : $this->_mapFinancesToProductsByCapId($productIds);

        $table = Mage::getModel('rockar_financingoptions/data_products')->getResource()
            ->getMainTable();
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $write->beginTransaction();

        try {
            $select = $write->select()
                ->from($table)
                ->where('product_id IN(?)', $productIds);

            $write->query($write->deleteFromSelect($select, $table));
            $chunkData = array_chunk($insertData, self::CHUNK_SIZE);

            foreach ($chunkData as $insertData) {
                $write->insertMultiple($table, $insertData);
            }

            $write->commit();
        } catch (Exception $e) {
            $write->rollback();
            throw ($e);
        }
    }

    /**
     * Maps finances to products by capId and vehicle condition
     *
     * @param array $productIds
     * @return array $insertData
     */
    protected function _mapFinancesToProductsWithCondition($productIds)
    {
        $productCollection = Mage::getModel('catalog/product')->getCollection()
            ->addFieldToFilter('entity_id', ['in' => $productIds])
            ->addAttributeToSelect(['cap_code', self::VEHICLE_CON_ATTRIBUTE_CODE]);

        $capCodes = [];

        foreach ($productCollection as $product) {
            $capCodes[$product->getCapCode()][$product->getAttributeText(self::VEHICLE_CON_ATTRIBUTE_CODE)][] = $product->getId();
        }

        $financeCollection = $this->getFinanceDataFromCapIds(array_keys($capCodes));
        $insertData = [];

        foreach ($financeCollection as $item) {
            if (isset($capCodes[$item->getCapid()][$item->getVehicleType()])) {
                foreach ($capCodes[$item->getCapid()][$item->getVehicleType()] as $productId) {
                    $insertData[] = [
                        'data_id'    => $item->getDataId(),
                        'product_id' => $productId
                    ];
                }
            }
        }

        unset ($capCodes, $productCollection, $financeCollection);

        return $insertData;
    }

    /**
     * Maps finances to products by capId
     *
     * @param array $productIds
     * @return array $insertData
     */
    protected function _mapFinancesToProductsByCapId($productIds)
    {
         $productCollection = Mage::getModel('catalog/product')->getCollection()
            ->addFieldToFilter('entity_id', ['in' => $productIds])
            ->addAttributeToSelect('cap_code');

        $capCodes = [];

        foreach ($productCollection as $product) {
            $capCodes[$product->getCapCode()][] = $product->getId();
        }

        $financeCollection = $this->getFinanceDataFromCapIds(array_keys($capCodes));
        $insertData = [];

        foreach ($financeCollection as $item) {
            foreach ($capCodes[$item->getCapid()] as $productId) {
                $insertData[] = [
                    'data_id'    => $item->getDataId(),
                    'product_id' => $productId
                ];
            }
        }

        unset ($capCodes, $productCollection, $financeCollection);

        return $insertData;
    }

    /**
     * Get finance data from capId
     *
     * @param string|array $capIds
     * @return Rockar_FinancingOptions_Model_Resource_Data_Collection
     */
    public function getFinanceDataFromCapIds($capIds)
    {
        if (!is_array($capIds)) {
            $capIds = [$capIds];
        }

        return Mage::getModel('rockar_financingoptions/data')->getCollection()
            ->addFieldToFilter('is_custom', 0)
            ->addFieldToFilter('capid', ['in' => $capIds]);
    }
}

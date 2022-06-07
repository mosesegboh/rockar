<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Importer
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Importer_Model_AccessoriesMapping extends Mage_Core_Model_Abstract
{
    /**
     * Callback method that will receive queue message and sending to be processed.
     *
     * @param string|array $msg
     *
     * @return void
     * @throws Mage_Core_Exception
     */
    public function processAccessoriesMapping($msg)
    {
        try {
            $actions = is_array($msg)
                ? $msg
                : (Mage::helper('rockar_all')->jsonDecode($msg) ?: []);
        } catch (Exception $e) {
            Mage::logException($e);
        }

        foreach ($actions as $action => $mappingData) {
            switch ($action) {
                case 'delete':
                    if (!empty($mappingData)) {
                        $this->deleteAccessoriesMappings($mappingData);
                    }
                    break;
                case 'update':
                case 'add':
                    if (!empty($mappingData)) {
                        $this->saveAccessoriesMappings($mappingData);
                    }
                    break;
            }
        }
    }

    /**
     * Save accessories mappings
     *
     * @param array $mappingData
     * @return void
     */
    public function saveAccessoriesMappings($mappingData)
    {
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        $accessoryModel = Mage::getModel('rockar_accessories/accessories');

        foreach ($mappingData as $accessoryCode => $products) {
            $accessoryModel->load($accessoryCode, 'identifier');

            if ($accessoryModel->getId()) {
                $data = [];

                foreach ($products as $sku) {
                    $data[] = [
                        'product_sku' => $sku,
                        'accessory_id' => $accessoryModel->getId()
                    ];
                }

                $writeConnection->insertOnDuplicate(
                    $resource->getTableName('rockar_accessories/accessories_relations'),
                    $data
                );
            } else {
                // Accessory is not available yet. Store mapping for later use
                $data = [];

                foreach ($products as $sku) {
                    $data[] = [
                        'product_sku' => $sku,
                        'accessory_identifier' => $accessoryCode
                    ];
                }

                $writeConnection->insertOnDuplicate(
                    $resource->getTableName('peppermint_importer/accessories_missing'),
                    $data
                );
            }
        }
    }

    /**
     * Delete accessories mappings
     *
     * @param array $mappingData
     * @return void
     */
    public function deleteAccessoriesMappings($mappingData)
    {
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');

        $accessoryModel = Mage::getModel('rockar_accessories/accessories');

        foreach ($mappingData as $accessoryCode => $products) {
            $accessoryModel->load($accessoryCode, 'identifier');

            if ($accessoryModel->getId()) {
                $writeConnection->delete(
                    $resource->getTableName('rockar_accessories/accessories_relations'),
                    $writeConnection->quoteInto('accessory_id = ? AND ', $accessoryModel->getId()) .
                    $writeConnection->quoteInto('product_sku IN (?)', $products)
                );
            } else {
                // Remove record from the missing accessories list
                $writeConnection->delete(
                    $resource->getTableName('peppermint_importer/accessories_missing'),
                    $writeConnection->quoteInto('accessory_identifier = ? AND ', $accessoryCode) .
                    $writeConnection->quoteInto('product_sku IN (?)', $products)
                );
            }
        }
    }
}

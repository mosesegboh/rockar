<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Catalog
 * @author    Jiraphong witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Catalog_Helper_Product_Flat extends Mage_Core_Helper_Abstract
{
    /**
     * Delete any mismatched entity ids and clear the flat tables
     * To allow product flat reindex to work | native Mage issue when deleting products
     *
     * @return void
     */
    public function flatTablesCleanUp()
    {
        $coreResource = Mage::getSingleton('core/resource');
        $readConnection = $coreResource->getConnection('core_read');
        $selectQuery = '';
        $queryIds = [];
        $data = [];

        $websiteIdQuery = "SELECT website_id FROM core_store WHERE code NOT LIKE '%admin%'";
        $websiteIds = $readConnection->fetchAll($websiteIdQuery);
        $count = count($websiteIds);
        $flatHelper = Mage::helper('catalog/product_flat');

        for ($i = 0; $i < $count; $i++) {
            $id = $websiteIds[$i]['website_id'];
            $flatTableName = $coreResource->getTableName('catalog/product_flat') . '_' . $id;

            if ($flatHelper->isBuilt($id)) {
                $selectQuery .= 'SELECT ' . $id . ' AS website, f.entity_id AS product FROM ' . $flatTableName
                    . ' AS f LEFT JOIN ' . $coreResource->getTableName('catalog/product')
                    . ' AS e ON f.entity_id = e.entity_id WHERE ISNULL(e.entity_id)';

                if ($i < $count - 1) {
                    $selectQuery .= ' UNION ';
                }
            }
        }

        if ($selectQuery) {
            $queryIds = $readConnection->fetchAll($selectQuery);
        }

        if ($queryIds) {
            foreach ($queryIds as ['website' => $websiteId, 'product' => $productId]) {
                $data[$websiteId][] = $productId;
            }
        }

        if ($data) {
            $writeConnection = $coreResource->getConnection('core_write');

            foreach ($data as $websiteId => $productIds) {
                try {
                    $writeConnection->delete(
                        $coreResource->getTableName('catalog/product_flat') . '_' . $websiteId,
                        $writeConnection->quoteInto('entity_id IN (?)', $productIds)
                    );
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }
        }
    }
}

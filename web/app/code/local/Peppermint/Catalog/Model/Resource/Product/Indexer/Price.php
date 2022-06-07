<?php
/**
 * @category Peppermint
 * @package Peppermint_Catalog
 * @author Osama Ahmed <osama.ahmed@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Catalog_Model_Resource_Product_Indexer_Price extends Mage_Catalog_Model_Resource_Product_Indexer_Price
{
    /**
     * Reindex product prices for specified product ids
     *
     * @param array | int $ids
     * @param bool $runGroupPriceReIndex
     * @return Mage_Catalog_Model_Resource_Product_Indexer_Price
     */
    public function reindexProductIds($ids, $runGroupPriceReIndex = false)
    {
        if (empty($ids)) {
            return $this;
        }

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $this->clearTemporaryIndexTable();
        $write  = $this->_getWriteAdapter();

        // retrieve products types
        $select = $write->select()
            ->from($this->getTable('catalog/product'), ['entity_id', 'type_id'])
            ->where('entity_id IN(?)', $ids);

        $pairs  = $write->fetchPairs($select);
        $byType = [];

        foreach ($pairs as $productId => $productType) {
            $byType[$productType][$productId] = $productId;
        }

        $compositeIds = [];
        $notCompositeIds = [];

        foreach ($byType as $productType => $entityIds) {
            $indexer = $this->_getIndexer($productType);

            if ($indexer->getIsComposite()) {
                $compositeIds += $entityIds;
            } else {
                $notCompositeIds += $entityIds;
            }
        }

        if (!empty($notCompositeIds)) {
            $select = $write->select()
                ->from(
                    ['l' => $this->getTable('catalog/product_relation')],
                    'parent_id'
                )->join(
                    ['e' => $this->getTable('catalog/product')],
                    'e.entity_id = l.parent_id',
                    ['type_id']
                )->where('l.child_id IN(?)', $notCompositeIds);

            $pairs  = $write->fetchPairs($select);

            foreach ($pairs as $productId => $productType) {
                if (!in_array($productId, $ids)) {
                    $ids[] = $productId;
                    $byType[$productType][$productId] = $productId;
                    $compositeIds[$productId] = $productId;
                }
            }
        }

        if (!empty($compositeIds)) {
            $this->_copyRelationIndexData($compositeIds, $notCompositeIds);

            if ($runGroupPriceReIndex) {
               $this->_prepareGroupPriceIndex($compositeIds, !$runGroupPriceReIndex);
            }
        }

        $indexers = $this->getTypeIndexers();

        foreach ($indexers as $indexer) {
            if (!empty($byType[$indexer->getTypeId()])) {
                $indexer->reindexEntity($byType[$indexer->getTypeId()]);
            }
        }

        $this->_copyIndexDataToMainTable($ids);

        return $this;
    }

    /**
     * Prepare group price index table
     *
     * @param int|array $entityIds the entity ids limitation
     * @param bool $isFullReindex
     * @return Mage_Catalog_Model_Resource_Product_Indexer_Price
     */
    protected function _prepareGroupPriceIndex($entityIds = null, $isFullReindex = true)
    {
        $write = $this->_getWriteAdapter();
        $table = $this->_getGroupPriceIndexTable();

        // Re-write here | Added a check if when running from update group price do not truncate the group price index table
        // Only delete specific product from index table
        if ($isFullReindex) {
            $write->delete($table);
        } elseif ($entityIds) {
            $write->delete(
                $table,
                ['entity_id IN(?)' => $entityIds]
            );
        }
        // Rewrite end

        $websiteExpression = $write->getCheckSql('gp.website_id = 0', 'ROUND(gp.value * cwd.rate, 4)', 'gp.value');
        $select = $write->select()
            ->from(
                ['gp' => $this->getValueTable('catalog/product', 'group_price')],
                ['entity_id'])
            ->join(
                ['cg' => $this->getTable('customer/customer_group')],
                'gp.all_groups = 1 OR (gp.all_groups = 0 AND gp.customer_group_id = cg.customer_group_id)',
                ['customer_group_id'])
            ->join(
                ['cw' => $this->getTable('core/website')],
                'gp.website_id = 0 OR gp.website_id = cw.website_id',
                ['website_id'])
            ->join(
                ['cwd' => $this->_getWebsiteDateTable()],
                'cw.website_id = cwd.website_id',
                [])
            ->where('cw.website_id != 0')
            ->columns(new Zend_Db_Expr("MIN({$websiteExpression})"))
            ->group(['gp.entity_id', 'cg.customer_group_id', 'cw.website_id']);

        if (!empty($entityIds)) {
            $select->where('gp.entity_id IN(?)', $entityIds);
        }

        $query = $select->insertFromSelect($table);
        $write->query($query);

        return $this;
    }
}

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Catalog
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Catalog_Model_Resource_Layer_Filter_Attribute extends Rockar_Carfinder_Model_Resource_Layer_Filter_Attribute
{
    /**
     * Apply attribute filter to product collection
     * {@inheritDoc}
     * Overridden to add filter by offer_tag_id which is not a product attribute
     */
    public function applyFilterToCollection($filter, $value)
    {
        if (!$this->allowMultipleSelect()) {
            // use original
            return parent::applyFilterToCollection($filter, $value);
        }

        $collection = $filter->getLayer()->getProductCollection();
        $attribute = $filter->getAttributeModel();

        if ($attribute->getAttributeCode() !== 'offer_tag_id') {
            return parent::applyFilterToCollection($filter, $value);
        }

        $offerTagsByProducts = Mage::registry('offer_tags_products') ?? [];

        if (!$offerTagsByProducts) {
            return $this;
        }

        $payment = Mage::helper('financing_options')->getActivePayment()['group_id'];
        $connection = $this->_getReadAdapter();
        $tableAlias = $attribute->getAttributeCode() . '_idx';
        $expressionCondition = "{$tableAlias}.value IN (?)";
        $conditionValue = $connection->quoteInto($expressionCondition, (array) $value);

        $offerTagIdsStatement = '(CASE ';

        foreach ($offerTagsByProducts as $entityId => $offerTagByFinanceProduct) {
            $offerTagId = isset($offerTagByFinanceProduct[$payment])
                ? $offerTagByFinanceProduct[$payment]->getId()
                : 'NULL';

            $offerTagIdsStatement .= " WHEN entity_id = {$entityId} THEN '{$offerTagId}'";
        }

        $offerTagIdsStatement .= ' END) as value';

        $tableName = $this->getTable('catalog/product_flat') . '_' . $filter->getStoreId();

        $subSelect = $connection->select()
            ->from([$tableAlias => $tableName], [
                'entity_id' => 'entity_id',
                'value' => $offerTagIdsStatement
            ]);

        $conditions = [
            "{$tableAlias}.entity_id = e.entity_id",
            $conditionValue
        ];

        $collection->getSelect()
            ->distinct(true)
            ->join(
                [$tableAlias => $subSelect],
                implode(' AND ', $conditions),
                []
            );
        // internal use, to mark this filter has been applied to collection
        $filter->setData('_filter_value', $value);

        return $this;
    }

    /**
     * Retrieve array with products counts per attribute option
     * {@inheritDoc}
     * Overridden to add filter by offer_tag_id which is not a product attribute
     */
    public function getCount($filter)
    {
        if (!$this->allowMultipleSelect()) {
            // use original
            return parent::getCount($filter);
        }

        $attribute = $filter->getAttributeModel();

        if ($attribute->getAttributeCode() !== 'offer_tag_id') {
            return $this->getCountWithConditions($filter);
        }

        $tableName = $this->getTable('catalog/product_flat') . '_' . $filter->getStoreId();
        $select = $this->_getBaseCountSelectSql($filter);
        $connection = $this->_getReadAdapter();
        $offerTagsByProducts = Mage::registry('offer_tags_products') ?? [];

        $payment = Mage::helper('financing_options')->getActivePayment()['group_id'];
        $offerTagsByProducts = array_filter($offerTagsByProducts, function ($item) use ($payment) {
            return isset($item[$payment]);
        });

        if (!$offerTagsByProducts) {
            return null;
        }

        $offerTagIdsStatement = '(CASE ';

        foreach ($offerTagsByProducts as $entityId => $offerTagByFinanceProduct) {
            $offerTagId = isset($offerTagByFinanceProduct[$payment])
                ? $offerTagByFinanceProduct[$payment]->getId()
                : 'NULL';

            $offerTagIdsStatement .= " WHEN entity_id = {$entityId} THEN '{$offerTagId}'";
        }

        $offerTagIdsStatement .= ' END) as value';

        $select->reset(Zend_Db_Select::GROUP);
        $tableAlias = 'tmp';

        $subSelect = $connection->select()
            ->from([$tableAlias => $tableName], [
                'entity_id' => 'entity_id',
                'value' => $offerTagIdsStatement
            ]);

        $conditions = [
            "{$tableAlias}.entity_id = e.entity_id",
            "{$tableAlias}.value is not null",
        ];

        $select
            ->distinct(true)
            ->join(
                [$tableAlias => $subSelect],
                join(' AND ', $conditions),
                ['value', 'count' => new Zend_Db_Expr("COUNT(DISTINCT {$tableAlias}.entity_id)")]
            )
            ->group("{$tableAlias}.value");

        return $connection->fetchPairs($select);
    }

    /**
     * Goes through product conditions and returns product ids which should be removed from filters selection
     *
     * @param array $items
     *
     * @return array
     */
    protected function removeItemsByMonthlyPrice($items)
    {
        $productIds = array_column($items, 'entity_id');
        $entityIdsToBeDeleted = [];

        $productCollection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('entity_id', ['in'=> $productIds])
            ->applyFrontendPriceLimitations();

        Mage::getSingleton('rockar_financingoptions/observer')->processCatalogProductCollection($productCollection, $entityIdsToBeDeleted);

        return $entityIdsToBeDeleted;
    }

    /**
     * Gets distinct products count for filters
     *
     * @param Mage_Catalog_Model_Layer_Filter_Attribute $filter
     *
     * @return array
     */
    protected function getCountWithConditions($filter)
    {
        if (!$this->allowMultipleSelect()) {
            // use original
            return parent::getCount($filter);
        }

        $select = $this->_getBaseCountSelectSql($filter);

        $connection = $this->_getReadAdapter();
        $attribute = $filter->getAttributeModel();
        $tableAlias = $filter->getTableAlias();

        $conditions = [
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
            $connection->quoteInto("{$tableAlias}.store_id = ?", $filter->getStoreId()),
        ];

        $select->reset(Zend_Db_Select::GROUP);

        $select
            ->distinct(true)
            ->join(
                [$tableAlias => $this->getMainTable()],
                join(' AND ', $conditions),
                ['value', "{$tableAlias}.entity_id"]
            );

        $entityIds = $this->removeItemsByMonthlyPrice($connection->fetchAll($select));

        if ($entityIds) {
            $oldWhere = $select->getPart(Varien_Db_Select::WHERE);

            if($oldWhere) {
                $oldWhere[] = ' And e.entity_id NOT IN (' . implode(',', array_keys($entityIds)) . ')';
                $select->setPart(Varien_Db_Select::WHERE, $oldWhere);
            } else {
                $select->setPart(Varien_Db_Select::WHERE, ['e.entity_id NOT IN (' . implode(',', array_keys($entityIds)) . ')']);
            }
        }

        $select->reset(Zend_Db_Select::COLUMNS);

        $select->columns(
            [
                "{$tableAlias}.value",
                'count' => new Zend_Db_Expr("COUNT(DISTINCT {$tableAlias}.entity_id)")
            ]
        )->group("{$tableAlias}.value");

        return $connection->fetchPairs($select);
    }
}

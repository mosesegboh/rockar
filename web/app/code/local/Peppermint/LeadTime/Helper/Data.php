<?php
/**
 * @category  Peppermint
 * @package   Peppermint_LeadTime
 * @author    Adrian Pescar <adrian.pescar@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_LeadTime_Helper_Data extends Rockar_LeadTime_Helper_Data
{
    const DEFAULT_LEAD_TIME = 7;

    /** @var int Order is not in process of creation for the reservation */
    const PLACING_ORDER_INACTIVE = 0;

    /** @var int Order is been created with the reservation */
    const PLACING_ORDER_ACTIVE = 1;

    /** @var int Order is been created with the reservation */
    const PLACING_ORDER_ERROR = 2;

    /**
     * Get shortest lead time for selected products
     *
     * @param Mage_Catalog_Model_Resource_Product_Collection $productCollection
     * @param array $filters
     *
     * @return integer
     * @throws Zend_Db_Select_Exception
     */
    public function getShortestLeadTimeForProducts(Mage_Catalog_Model_Resource_Product_Collection $productCollection, array $filters = [])
    {
        /**
         * Take product collection select and reset result
         */
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');

        $leadTimeColumn = 'cheapest_product_lead_time';
        if ($isFlat = $productCollection->isEnabledFlat()) {
            $select = clone $productCollection->getSelect();
        } else if (!empty($filters) && !$isFlat) {
            $select = clone $productCollection->addAttributeToSelect(array_merge(array_keys($filters), [$leadTimeColumn]), 'join')
                ->getSelect();
        } else {
            $select = clone $productCollection->addAttributeToSelect($leadTimeColumn, 'left')
                ->getSelect();
        }

        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);

        /**
         * Select only lead time
         */
        $select->columns(
                [
                    'lead_time' => $isFlat ? "e.{$leadTimeColumn}" : "at_{$leadTimeColumn}.value",
                ]
            )
            ->order('lead_time ASC')
            ->limit(1);

        foreach ($filters as $filter => $value) {
            /**
             * Need to reset where and from (join) condition if it already exists for filter, for example visible_in
             */
            $where = $select->getPart(Zend_Db_Select::WHERE);
            foreach ($where as $i => $condition) {
                if (strpos($condition, $filter) !== false) {
                    unset($where[$i]);
                }
            }
            $select->setPart(Zend_Db_Select::WHERE, $where);

            $from = $select->getPart(Zend_Db_Select::FROM);
            foreach ($from as $i => $condition) {
                if ($i === "{$filter}_idx") {
                    unset($from[$i]);
                }
            }
            $select->setPart(Zend_Db_Select::FROM, $from);

            if ($isFlat) {
                if (!is_int($value)) {
                    $value = "'$value'";
                }
                $select->where("$filter = $value");
            } else {
                $select->where("at_$filter.value = $value");
            }
        }

        $leadTime = $connection->fetchOne($select);

        return (int)$leadTime;
    }

    /**
     * Removes vin reservation
     *
     * @param int $customerId
     *
     * @return void
     */
    public function removeVinReservation($customerId = null)
    {
        if (!$customerId) {
            return;
        }

        $collection = Mage::getModel('peppermint_leadtime/reservation')->getCollection()
            ->addFieldToFilter('customer_id', $customerId);

        foreach ($collection->getItems() as $item) {
            $item->delete();
        }
    }

    /**
     * Get custom lead time configurations
     *
     * @param Peppermint_Catalog_Model_Product|null $product
     *
     * @return mixed
     */
    public function getCustomLeadTimeConfigurations($product = null)
    {
        // this functionality should only apply to simple products so if it's a configurable return null
        if (!$product || $product->getTypeId() !== Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
            return [];
        }

        $identifier = $this->_getLeadTimeProductIdentifier($product);

        // if the identifier is null for any reason return so that
        // we don't save the whole collection against _customLeadTimeConfigurations
        if (!$identifier) {
            return [];
        }

        if (!array_key_exists($identifier, $this->_customLeadTimeConfigurations)) {
            $collection = Mage::getSingleton('rockar_lead_time/lead_time')->getCollection()
                ->addFieldToFilter('identifier', $identifier);

            $this->_customLeadTimeConfigurations[$identifier] = $collection->getItems();
        }

        return $this->_customLeadTimeConfigurations[$identifier];
    }

    /**
     * Filter array of identifier which is not in the lead time table
     *
     * @param array $vinArr
     *
     * @return array
     */
    public function filterMissingVin(array $vinArr): array
    {
        $result = [];

        if (!empty($vinArr)) {
            $coreResource = Mage::getSingleton('core/resource');
            $readAdapter = $coreResource->getConnection('core_read');

            $select = $readAdapter->select()
                ->from($coreResource->getTableName('rockar_lead_time/lead_time'), ['identifier'])
                ->where('identifier IN (?)', $vinArr);

            foreach ($readAdapter->fetchAll($select) as ['identifier' => $identifer]) {
                $result[] = $identifer;
            }
        }

        return $result;
    }

    /**
     * Get Cheapest Product LeadTime Value
     *
     * @param int|Mage_Catalog_Model_Product $product
     * @param bool $showInDays
     *
     * @return int|null
     */
    public function getCheapestProductLeadTime($product, $showInDays = true)
    {
        if ($product && is_numeric($product)) {
            $product = Mage::getModel('catalog/product')->load($product);
        }

        $leadTime = $product->getCheapestProductLeadTime() ?: Mage::getResourceModel('catalog/product')->getAttributeRawValue(
            $product->getId(),
            'cheapest_product_lead_time',
            Mage::app()->getStore()->getId()
        );

        return $leadTime
            ? (int) ($showInDays ? ($leadTime * static::LEAD_TIME_DAYS_PER_WEEK) : $leadTime)
            : null;
    }
}

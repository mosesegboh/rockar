<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Reports
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Reports_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * XML PATHS
     */
    const XML_PATH_REPORTS_VIN_PRODUCT_PRICING_ORDER_STATUS = 'peppermint_reports/vin_product_pricing_report/order_status';
    const XML_PATH_REPORTS_VIN_PRODUCT_PRICING_DAYS_TRANSFER_TO_ARCHIVE = 'peppermint_reports/vin_product_pricing_report/days_transfer_to_archive';

    /**
     * ACTIONS
     */
    const ACTION_IMPORT = 'import'; //temporary action for both publish and price_change
    const ACTION_PUBLISH = 'Publish';
    const ACTION_UNPUBLISH = 'Un-Publish';
    const ACTION_PRICE_CHANGE = 'Price Change';
    const ACTION_PRICE_RULE_APPLIED = 'Price Updated by a Catalog Price Rule';
    const ACTION_ORDER_STATUS_CHANGE = 'Order Status Change';

    /**
     * Chunk
     */
    const CHUNK_LENGTH = 5000;

    /** @var Mage_Core_Model_Resource */
    private $_resource;

    /** @var Varien_Db_Adapter_Interface */
    private $_writeConnection;

    /** @var Varien_Db_Adapter_Interface */
    private $_readConnection;

    /** @var array */
    private $_attributes;

    private $_logAttributes = [
        'price',
        'mplan_price',
        'co2_tax',
        'published_to_ds_date',
        'vehicle_condition',
        'cap_code'
    ];

    private $logUnchanged = [
        self::ACTION_ORDER_STATUS_CHANGE,
        self::ACTION_UNPUBLISH
    ];

    private $_snapshotTable;
    private $_logTable;

    /**
     * Peppermint_Reports_Helper_Data constructor.
     */
    public function __construct()
    {
        $this->_resource = Mage::getSingleton('core/resource');
        $this->_writeConnection = $this->_resource
            ->getConnection('core_write');
        $this->_readConnection = $this->_resource
            ->getConnection('core_read');

        $this->_attributes = $this->_getAttributesIdsByCodes(['price', 'vin_number']);
        $this->_snapshotTable = $this->_resource
            ->getTableName('peppermint_reports/vin_product_pricing_snapshot');

        $this->_logTable = $this->_resource
            ->getTableName('peppermint_reports/vin_product_pricing_log');
    }

    /**
     * Get order statuses to log price change
     *
     * @return mixed
     */
    public function getOrderStatuses()
    {
        return explode(',', Mage::getStoreConfig(self::XML_PATH_REPORTS_VIN_PRODUCT_PRICING_ORDER_STATUS));
    }

    /**
     * Get number of days when transfer vin pricing logs to archive
     *
     * @return int
     */
    public function getDaysTransferToArchive()
    {
        return (int) Mage::getStoreConfig(self::XML_PATH_REPORTS_VIN_PRODUCT_PRICING_DAYS_TRANSFER_TO_ARCHIVE);
    }

    /**
     * Log price change
     *
     * @param $productIds
     * @param $action
     * @param string $realAction
     * @return void
     */
    public function logVinPrice($productIds, $action, $realAction = null)
    {
        $newPrices = $this->_getNewProductsPrices($productIds);
        $snapshotData = $this->_getSnapshotPrices($productIds);

        $updateSnapshots = [];
        $deleteSnapshots = [];
        $logProducts = [];

        foreach ($newPrices as $key => $newPrice) {
            if (in_array($action, $this->logUnchanged)) {
                $logProducts[$newPrice['product_id']][] = $newPrice['customer_group_id'];

                continue;
            }

            if (!isset($snapshotData[$key]['final_price'])) {
                $logProducts[$newPrice['product_id']][] = $newPrice['customer_group_id'];
                $action = $action !== self::ACTION_IMPORT ? $action : self::ACTION_PUBLISH;
                $updateSnapshots[$key] = [
                    'final_price' => $newPrice['final_price'],
                    'product_id' => $newPrice['product_id'],
                    'customer_group_id' => $newPrice['customer_group_id'],
                    'vin_number' => $newPrice['vin_number'],
                ];
            } else if ($snapshotData[$key]['final_price'] !== $newPrice['final_price']) {
                $action = $action !== self::ACTION_IMPORT ? $action : self::ACTION_PRICE_CHANGE;
                $deleteSnapshots[] = $snapshotData[$key]['id'];
                $updateSnapshots[$key] = array_diff_key($snapshotData[$key], array_flip(['id', 'grouped_id']));
                $updateSnapshots[$key]['final_price'] = $newPrice['final_price'];
                $logProducts[$newPrice['product_id']][] = $newPrice['customer_group_id'];
            }
        }

        if (!empty($logProducts)) {
            $this->_doLogPriceChange($logProducts, $action, $realAction);
            $this->_updatePriceSnapshot($deleteSnapshots, $updateSnapshots);
        }
    }

    /**
     * Populate snapshot table with data
     *
     * @return void
     */
    public function fillVinPricingSnapshot()
    {
        try {
            $select = $this->getCurrentPricesSelect();
            $this->_writeConnection
                ->truncateTable($this->_snapshotTable);

            $this->_writeConnection->query(
                $select->insertFromSelect($this->_snapshotTable, [
                    'product_id',
                    'vin_number',
                    'final_price',
                    'customer_group_id'
                ])
            );
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Prepare query to retrieve current prices
     *
     * @param array|int $productIds
     * @param array|int $customerGroups
     * @return Varien_Db_Select
     */
    public function getCurrentPricesSelect($productIds = null, $customerGroups = null)
    {
        $select = $this->_readConnection
            ->select()
            ->from(['cpe' => $this->_resource->getTableName('catalog/product')], [])
            ->columns([
                'product_id' => 'cpe.entity_id',
                'vin_number' => 'cpev.value',
                'final_price' => $this->_readConnection->getIfNullSql('cpp.rule_price', 'cpd.value'),
                'customer_group_id' => 'cg.customer_group_id',
            ])
            ->joinLeft(
                ['cg' => $this->_resource->getTableName('customer/customer_group')],
                'true',
                []
            )
            ->joinInner(
                ['cpev' => 'catalog_product_entity_varchar'],
                'cpe.entity_id = cpev.entity_id AND cpev.attribute_id = ' . $this->_attributes['vin_number'],
                []
            )
            ->joinInner(
                ['cpd' => 'catalog_product_entity_decimal'],
                'cpe.entity_id = cpd.entity_id AND cpd.attribute_id = ' . $this->_attributes['price'],
                []
            )
            ->joinLeft(
                ['cpp' => $this->_resource->getTableName('catalogrule/rule_product_price')],
                'cpp.product_id = cpe.entity_id AND rule_date = DATE(NOW()) AND cg.customer_group_id = cpp.customer_group_id',
                []
            )
            ->where('cpe.type_id = ?', 'simple');

        if ($productIds) {
            $productIds = (array) $productIds;
            $select->where('cpe.entity_id IN (?)', $productIds);
        }

        if ($customerGroups) {
            $customerGroups = (array) $customerGroups;
            $select->where('cg.customer_group_id IN (?)', $customerGroups);
        }

        return $select;
    }

    /**
     * Get attribute Ids array keyed by attribute codes
     *
     * @param $codes
     * @return array
     */
    protected function _getAttributesIdsByCodes($codes)
    {
        $codes = (array) $codes;
        $select = $this->_readConnection
            ->select()
            ->from($this->_resource->getTableName('eav/attribute'), ['attribute_code', 'attribute_id'])
            ->where('attribute_code in (?)', $codes);

        return $this->_readConnection
            ->fetchPairs($select);
    }

    /**
     * Prepare price change data and log it
     *
     * @param array  $productsToLog
     * @param string $action
     * @param string $realAction
     */
    protected function _doLogPriceChange(array $productsToLog, $action, $realAction = null): void
    {
        $productLogDataQuery = $this->getCurrentPricesSelect(array_keys($productsToLog));
        $productLogDataQuery->columns(['customer_group' => 'cg.customer_group_code']);

        foreach ($this->_logAttributes as $attributeCode) {
            $this->_joinInnerAttribute($productLogDataQuery, $attributeCode, 'cpe');
        }

        $this->_joinCatalogRules($productLogDataQuery);
        $logData = $this->_readConnection->fetchAll($productLogDataQuery);
        $insertArray = [];

        foreach ($logData as &$data) {
            if (!in_array($data['customer_group_id'], $productsToLog[$data['product_id']])) {
                continue;
            }

            $ruleId = $data['rule_id'] ?? null;
            $data = $this->_combineRuleData($data);
            $data['action'] = $realAction ?? $action;

            $key = $data['product_id'] . '-' . $data['customer_group_id'];

            if (isset($insertArray[$key])) {
                if (isset($ruleId)) {
                    $insertArray[$key]['price_rules'][$ruleId] = $data['price_rules'][$ruleId];
                }
            } else {
                $insertArray[$key] = $data;
            }
        }

        array_walk($insertArray, function (&$item) {
            $item['price_rules'] = is_array($item['price_rules'])
                ? json_encode($item['price_rules'])
                : $item['price_rules'];
        });

        foreach (array_chunk($insertArray, self::CHUNK_LENGTH) as $insertChunk) {
            $this->_writeConnection
                ->insertMultiple($this->_logTable, $insertChunk);
        }
    }

    /**
     * Joins product attributes to price query select
     *
     * @param Varien_Db_Select $productLogDataQuery
     * @param string           $attributeCode
     * @param string           $refAlias
     * @return void
     */
    protected function _joinInnerAttribute(Varien_Db_Select $productLogDataQuery, string $attributeCode, $refAlias = 'cpe'): void
    {
        $alias = $attributeCode . '_table';
        /** @var Mage_Catalog_Model_Entity_Attribute $attribute */
        $attribute = Mage::getSingleton('eav/config')
            ->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attributeCode);

        if ($attribute->getFrontendInput() === 'select') {
            /** Adding eav attribute value */
            $productLogDataQuery->joinInner(
                [$alias => $attribute->getBackendTable()],
                "{$refAlias}.entity_id   = $alias.entity_id AND $alias.attribute_id={$attribute->getId()}",
                []
            )
                ->joinInner(
                    [$alias . '_option' => 'eav_attribute_option_value'],
                    "{$alias}_option.option_id = $alias.value and {$alias}_option.store_id = 0",
                    [$attributeCode => 'value']
                );
        } else {
            /** Adding eav attribute value */
            $productLogDataQuery->joinInner(
                [$alias => $attribute->getBackendTable()],
                "{$refAlias}.entity_id   = $alias.entity_id AND $alias.attribute_id={$attribute->getId()}",
                [$attributeCode => 'value']
            );
        }
    }

    /**
     * Update price snapshot table
     *
     * @param array $snapshotsToDelete
     * @param array $updatedSnapshotData
     * @return void
     */
    protected function _updatePriceSnapshot(array $snapshotsToDelete, array $updatedSnapshotData): void
    {
        if (!empty($snapshotsToDelete)) {
            $this->_writeConnection->delete(
                $this->_snapshotTable,
                ['id in (?)' => $snapshotsToDelete]
            );
        }

        if (!empty($updatedSnapshotData)) {
            foreach (array_chunk($updatedSnapshotData, self::CHUNK_LENGTH) as $insertChunk) {
                $this->_writeConnection
                    ->insertMultiple($this->_snapshotTable, $insertChunk);
            }
        }
    }

    /**
     * Get updated prices data for the products from eav
     *
     * @param $productIds
     * @return array
     */
    protected function _getNewProductsPrices($productIds): array
    {
        $pricesSelect = $this->_readConnection
            ->select()
            ->from($this->getCurrentPricesSelect($productIds))
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns([
                'grouped_id' => $this->_readConnection->getConcatSql(
                    ['product_id', 'customer_group_id'],
                    '-'
                ),
            ])
            ->columns('*');

        $newPrices = $this->_readConnection
            ->fetchAssoc($pricesSelect);

        return $newPrices;
    }

    /**
     * Get previous prices data for the products from price snapshot
     *
     * @param $productIds
     * @return array
     */
    protected function _getSnapshotPrices($productIds): array
    {
        $snapshotSelect = $this->_readConnection
            ->select()
            ->from(
                ['snap' => $this->_snapshotTable],
                ['grouped_id' => $this->_readConnection->getConcatSql(
                    ['product_id', 'customer_group_id'],
                    '-'
                )]
            )
            ->columns('*');

        if ($productIds) {
            $snapshotSelect->where('product_id in (?)', $productIds);
        }

        $snapshotData = $this->_readConnection
            ->fetchAssoc($snapshotSelect);

        return $snapshotData;
    }

    /**
     * Join catalog rules data to price query select
     *
     * @param Varien_Db_Select $productLogDataQuery
     * @return void
     */
    protected function _joinCatalogRules(Varien_Db_Select $productLogDataQuery): void
    {
        $productLogDataQuery->joinLeft(
            ['rp' => $this->_resource->getTableName('catalogrule/rule_product')],
            'cpe.entity_id = rp.product_id and cg.customer_group_id = rp.customer_group_id',
            []
        )
            ->joinLeft(
                ['rule' => $this->_resource->getTableName('catalogrule/rule')],
                'rp.rule_id = rule.rule_id',
                [
                    'rule_id' => 'rule_id',
                    'rule_name' => 'name',
                    'rule_apply' => 'simple_action',
                    'rule_discount_amount' => 'discount_amount',
                    'rule_priority' => 'sort_order'
                ]
            );
    }

    /**
     * Combines catalog price rule data to subarray price_rules
     *
     * @param $data
     * @return array
     */
    protected function _combineRuleData($data): array
    {
        if (!empty($data['rule_id'])) {
            $data['price_rules'][$data['rule_id']] = [
                'rule_id' => $data['rule_id'],
                'rule_name' => $data['rule_name'],
                'rule_apply' => $data['rule_apply'],
                'rule_discount_amount' => $data['rule_discount_amount'],
                'rule_priority' => $data['rule_priority']
            ];
        } else {
            $data['price_rules'] = 'No rules applied';
        }

        unset(
            $data['rule_id'],
            $data['rule_name'],
            $data['rule_apply'],
            $data['rule_discount_amount'],
            $data['rule_priority']
        );

        return $data;
    }
}

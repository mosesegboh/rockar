<?php
/**
 * @category  Peppermint
 * @package   Peppermint_LeadTime
 * @author    Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_LeadTime_Model_Observer_Product extends Rockar_LeadTime_Model_Observer_Product
{
    const GROUP_PRICE_UPDATE_CRON_JOB = 'peppermint_group_price_recompile';

    /**
     * Product count limit for full price reindex
     */
    const APPLY_FULL_REINDEX_COUNT_LIMIT = 90;

    /**
     * Update all product availability.
     *
     * @param bool $doReindex schedule flat reindex cron
     * @return void
     */
    public function updateAllProductStatuses($doReindex = true)
    {
        if (!Mage::registry('peppermint_import') && !Mage::registry('peppermint_single_product_status_update')) {
            try {
                $resource = Mage::getSingleton('core/resource');
                $writeConnection = $resource->getConnection('core_write');

                $disabledProductsIds = $this->_getNotVisibleProducts();

                // Simple products
                $visibleInCatalogSimpleIds = $this->_getSimpleProducts(
                    Rockar_YouDrive_Model_Product_Attribute_Source_Visiblein::CATALOG
                );
                $visibleInCatalogSimpleIds = array_diff($visibleInCatalogSimpleIds, $disabledProductsIds);
                $visibleInCatalogSimpleIds = empty($visibleInCatalogSimpleIds) ? [0] : $visibleInCatalogSimpleIds;
                $visibleInYoudriveSimpleIds = $this->_getSimpleProducts(
                    Rockar_YouDrive_Model_Product_Attribute_Source_Visiblein::YOUDRIVE
                );
                $visibleInYoudriveSimpleIds = array_diff($visibleInYoudriveSimpleIds, $disabledProductsIds);
                $visibleInYoudriveSimpleIds = empty($visibleInYoudriveSimpleIds) ? [0] : $visibleInYoudriveSimpleIds;
                $visibleInCatalogYoudriveSimpleIds = $this->_getSimpleProducts(
                    Rockar_YouDrive_Model_Product_Attribute_Source_Visiblein::CATALOG_AND_YOUDRIVE
                );
                $visibleInCatalogYoudriveSimpleIds = array_diff($visibleInCatalogYoudriveSimpleIds,
                    $disabledProductsIds);
                $visibleInCatalogYoudriveSimpleIds = empty($visibleInCatalogYoudriveSimpleIds)
                    ? [0]
                    : $visibleInCatalogYoudriveSimpleIds;

                // Configurable products
                $visibleInCatalogConfigurableIds = $this->_getConfigurableProducts($visibleInCatalogSimpleIds);
                $visibleInYoudriveConfigurableIds = $this->_getConfigurableProducts($visibleInYoudriveSimpleIds);

                $visibleInCatalogYoudriveConfigurableIds = array_merge(
                    $this->_getConfigurableProducts($visibleInCatalogYoudriveSimpleIds),
                    array_intersect($visibleInCatalogConfigurableIds, $visibleInYoudriveConfigurableIds)
                );

                // All simple products that should be enabled
                $enabledSimpleProductIds = array_merge(
                    $visibleInCatalogSimpleIds,
                    $visibleInYoudriveSimpleIds,
                    $visibleInCatalogYoudriveSimpleIds
                );
                $enabledSimpleProductIds = array_diff($enabledSimpleProductIds, $disabledProductsIds);
                $enabledSimpleProductIds = empty($enabledSimpleProductIds) ? [0] : $enabledSimpleProductIds;

                // All configurable products that should be enabled (have at least 1 enabled child product)
                $enabledConfigurableProductIds = array_merge(
                    $visibleInCatalogConfigurableIds,
                    $visibleInYoudriveConfigurableIds,
                    $visibleInCatalogYoudriveConfigurableIds
                );
                $enabledConfigurableProductIds = array_diff($enabledConfigurableProductIds, $disabledProductsIds);
                $enabledConfigurableProductIds = empty($enabledConfigurableProductIds)
                    ? [0]
                    : $enabledConfigurableProductIds;

                // All products that should be enabled(simple + configurable)
                $enabledProductIds = array_merge($enabledSimpleProductIds, $enabledConfigurableProductIds);

                /**
                 * Enable/set visible_in for products
                 */
                /** @var Mage_Eav_Model_Config $eavModel */
                $eavModel = Mage::getModel('eav/config');

                $statusAttribute = $eavModel->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'status');
                $visibleInAttribute = $eavModel->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'visible_in');

                // enable only products that currently have disabled status
                $productIdsToEnable = $this->_getProductsToUpdateStatus($enabledProductIds,
                    Mage_Catalog_Model_Product_Status::STATUS_DISABLED);

                if ($productIdsToEnable) {
                    $writeConnection->update(
                        $statusAttribute->getBackendTable(),
                        ['value' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED],
                        'attribute_id = ' . $statusAttribute->getId()
                        . ' AND entity_id IN(' . implode(',', $productIdsToEnable) . ')'
                    );
                }

                // Set product visible_in value

                // ---------------------- Simple products --------------------------
                // CATALOG
                $writeConnection->update(
                    $visibleInAttribute->getBackendTable(),
                    ['value' => Rockar_YouDrive_Model_Product_Attribute_Source_Visiblein::CATALOG],
                    'attribute_id = ' . $visibleInAttribute->getId()
                    . ' AND entity_id IN(' . implode(',', $visibleInCatalogSimpleIds) . ')'
                );

                // YOUDRIVE
                $writeConnection->update(
                    $visibleInAttribute->getBackendTable(),
                    ['value' => Rockar_YouDrive_Model_Product_Attribute_Source_Visiblein::YOUDRIVE],
                    'attribute_id = ' . $visibleInAttribute->getId()
                    . ' AND entity_id IN(' . implode(',', $visibleInYoudriveSimpleIds) . ')'
                );

                // CATALOG_AND_YOUDRIVE
                $writeConnection->update(
                    $visibleInAttribute->getBackendTable(),
                    ['value' => Rockar_YouDrive_Model_Product_Attribute_Source_Visiblein::CATALOG_AND_YOUDRIVE],
                    'attribute_id = ' . $visibleInAttribute->getId()
                    . ' AND entity_id IN(' . implode(',', $visibleInCatalogYoudriveSimpleIds) . ')'
                );

                // --------------- Configurable products --------------------
                // CATALOG
                $writeConnection->update(
                    $visibleInAttribute->getBackendTable(),
                    ['value' => Rockar_YouDrive_Model_Product_Attribute_Source_Visiblein::CATALOG],
                    'attribute_id = ' . $visibleInAttribute->getId()
                    . ' AND entity_id IN(' . implode(',', $visibleInCatalogConfigurableIds) . ')'
                );

                // YOUDRIVE
                $writeConnection->update(
                    $visibleInAttribute->getBackendTable(),
                    ['value' => Rockar_YouDrive_Model_Product_Attribute_Source_Visiblein::YOUDRIVE],
                    'attribute_id = ' . $visibleInAttribute->getId()
                    . ' AND entity_id IN(' . implode(',', $visibleInYoudriveConfigurableIds) . ')'
                );

                // CATALOG_AND_YOUDRIVE
                $writeConnection->update(
                    $visibleInAttribute->getBackendTable(),
                    ['value' => Rockar_YouDrive_Model_Product_Attribute_Source_Visiblein::CATALOG_AND_YOUDRIVE],
                    'attribute_id = ' . $visibleInAttribute->getId()
                    . ' AND entity_id IN(' . implode(',', $visibleInCatalogYoudriveConfigurableIds) . ')'
                );


                // disable only products that currently have enabled status
                $productIdsToDisable = $this->_getProductsToUpdateStatus($enabledProductIds,
                    Mage_Catalog_Model_Product_Status::STATUS_ENABLED);

                if ($productIdsToDisable) {
                    $writeConnection->update(
                        $statusAttribute->getBackendTable(),
                        ['value' => Mage_Catalog_Model_Product_Status::STATUS_DISABLED],
                        'attribute_id = ' . $statusAttribute->getId()
                        . ' AND entity_id IN(' . implode(',', $productIdsToDisable) . ')'
                    );
                }

                if ($doReindex && !Mage::registry('product_import')) {
                    $this->scheduleReindexFlat();
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addNotice(
                    Mage::helper('rockar_lead_time')->__('Item was saved, but an error occurred while updating product availability based on lead times.')
                );
                Mage::logException($e);
            }
        }
    }

    /**
     * Update product availability after lead time save
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function updateProductStatusesAfterLeadTimeSave(Varien_Event_Observer $observer)
    {
        $isSingleStatusUpdate = Mage::registry('peppermint_single_product_status_update');
        $eventName = $observer->getEvent()->getName();
        $isOrderExpiredOrImport = ($eventName === 'peppermint_order_expired_import_after'
            || $eventName === 'peppermint_import_availability_after'
        );
        $vins = $observer->getEvent()->getData('importedVins');

        if (!Mage::registry('lead_time_import') && !$isSingleStatusUpdate) {
            // do not update individual lead time in import
            $this->updateAllProductStatuses(!$isOrderExpiredOrImport);

            if ($isOrderExpiredOrImport && $vins && count($vins) > static::APPLY_FULL_REINDEX_COUNT_LIMIT) {
                Mage::getModel('peppermint_catalog/cron')->run();
                $this->scheduleReindexFlat();

                return;
            }
        } elseif ($isSingleStatusUpdate && !Mage::registry('peppermint_order_expired_import')) {
            // do not index single product during order expired import
            // run for order cancel from ecp
            $this->orderCancelUpdateSingleProductStatuses($observer);
        }

        // do not reindex each product separately in import and avoid for checkout
        if (
            !Mage::registry('lead_time_import')
            && !Mage::registry('peppermint_import')
        ) {
            $leadTime = $observer->getDataObject();

            if ($leadTime) {
                $vinFilter = ['like' => $leadTime->getIdentifier() . '%'];
            } else if ($vins) {
                $vinFilter = array_map(function($vin) {
                    return ['like' => $vin . '%'];
                }, $vins);
            } else {
                return;
            }

            $productIds = Mage::getModel('catalog/product')->getCollection()
                ->addFieldToFilter('sku', $vinFilter)
                ->getAllIds();

            $productModel = Mage::getModel('catalog/product');
            $vehicleHelper = Mage::helper('peppermint_catalog/vehicle');

            if ($productIds) {
                $typeIdsArray = Mage::helper('peppermint_catalog')->getProductTypeIds($productIds);

                foreach ($productIds as $id) {
                    if (isset($typeIdsArray[$id]) && $typeIdsArray[$id] === Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
                        $vehicleHelper->updateConfigurableGroupPrice(
                            $vehicleHelper->getConfigurableFromSimple($id)
                        );
                    }
                }
            }
        }
    }

    /**
     * Index product attribute and category after order import
     * Event: peppermint_import_availability_after
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function indexProductAttributesAfterLeadTimeImport(Varien_Event_Observer $observer)
    {
        // Do not reindex products during import
        if (
            !Mage::registry('lead_time_import')
            && !Mage::registry('peppermint_import')
            && $vins = $observer->getEvent()->getData('importedVins')
        ) {
            $coreResource = Mage::getSingleton('core/resource');
            $readAdapter = $coreResource->getConnection('core_read');

            try {
                $select = $readAdapter->select()
                    ->from(['main_table' => $coreResource->getTableName('catalog/product')], ['entity_id'])
                    ->join(
                        ['link_table' => $coreResource->getTableName('catalog/product_super_link')],
                        'link_table.product_id = main_table.entity_id',
                        'parent_id'
                    )
                    ->where('sku IN (?)', $vins);

                $parentIds = array_values($readAdapter->fetchPairs($select));

                if ($parentIds) {
                    Mage::getResourceModel('catalog/category_indexer_product')->catalogProductMassAction(
                        Mage::getModel('index/event')->setNewData(['product_ids' => $parentIds])
                    );
                    Mage::getResourceModel('catalog/product_indexer_eav_source')->reindexEntities($parentIds);
                }
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }

    /**
     * Update single product availability and status after order cancel
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function orderCancelUpdateSingleProductStatuses(Varien_Event_Observer $observer)
    {
        $leadTime = $observer->getDataObject();
        $vin = $leadTime ? $leadTime->getIdentifier() : null;

        try {
            if (!empty($vin)) {
                $simpleProduct = Mage::getModel('catalog/product')->getCollection()
                    ->addAttributeToSelect('status')
                    ->addFieldToFilter('sku', $vin)
                    ->setCurPage(1)
                    ->setPageSize(1)
                    ->getFirstItem();

                $simpleProductId = $simpleProduct->getId();

                if ($simpleProductId && !$simpleProduct->isInStock()) {
                    // Update simple product
                    $resource = Mage::getSingleton('core/resource');
                    $writeConnection = $resource->getConnection('core_write');
                    $statusAttribute = Mage::getModel('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'status');

                    $writeConnection->update(
                        $statusAttribute->getBackendTable(),
                        ['value' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED],
                        'attribute_id = ' . $statusAttribute->getId()
                        . ' AND entity_id = ' . $simpleProductId
                    );

                    $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($simpleProductId);
                    // update configurable product
                    if (isset($parentIds[0])) {
                        $configurableProduct = Mage::getModel('catalog/product')->load($parentIds[0]);
                        $productsUpdater = Mage::getModel('rockar_catalog/catalog_product_update_configurable')
                            ->setProducts($configurableProduct, $simpleProduct)
                            ->updateVisibilityAndStatus();
                    }

                    if (!Mage::registry('product_import')
                        && !Mage::registry('peppermint_import')
                        && !Mage::registry('peppermint_order_expired_import')
                    ) {
                        $this->scheduleReindexFlat();
                    }
                }
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Get products to update enabled status
     *
     * @param $productIds
     * @param $status
     * @return array
     */
    protected function _getProductsToUpdateStatus($productIds, $status)
    {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');

        /**
         * Select products which should be enabled.
         */
        $statusAttribute = Mage::getModel('eav/config')->getAttribute(
            Mage_Catalog_Model_Product::ENTITY,
            'status'
        );

        if ($status === Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {
            $where = 'product.entity_id IN (' . implode(',', $productIds) . ') AND status.value = ' . $status;
        } else {
            $where = 'product.entity_id NOT IN (' . implode(',', $productIds) . ') AND status.value = ' . $status;
        }

        $select = $readConnection->select()
            ->from(['product' => $resource->getTableName('catalog/product')], ['product_id' => 'entity_id'])
            ->joinLeft(
                ['status' => $statusAttribute->getBackendTable()],
                'status.entity_id = product.entity_id AND status.attribute_id = ' . $statusAttribute->getId(),
                ['status' => 'status.value']
            )->where($where);

        $updateProducts = $readConnection->fetchAll($select);

        return is_array($updateProducts)
            ? array_column($updateProducts, 'product_id')
            : [];
    }

    /**
     * Run indexer to update flat tables for FE
     *
     * @param int $time timestamp
     * @return void
     */
    public function scheduleReindexFlat($time = null)
    {
        $this->scheduleCronJob('rockar_catalog_product_flat_reindex_all', $time);
    }

    /**
     * Schedule a cron job
     *
     * @param string $productFlatCronCode
     * @param int $time timestamp
     *
     * @return void
     */
    public function scheduleCronJob($productFlatCronCode, $time = null)
    {
        if (!$this->isJobCodeAlreadyScheduled($productFlatCronCode)) {
            Mage::getModel('cron/schedule')->setJobCode($productFlatCronCode)
                ->setScheduledReason(Aoe_Scheduler_Model_Schedule::REASON_SCHEDULENOW_CLI)
                ->schedule($time)
                ->save();
        }
    }

    /**
     * Returns lead time id by order id
     *
     * @param Peppermint_Sales_Model_Order|int $order
     *
     * @param bool $checkCanceled
     * @return string|null|false
     */
    protected function _getOrderLeadTimeId($order, $checkCanceled = false)
    {
        if (!$order) {
            return null;
        }

        if (is_numeric($order)) {
            $order = Mage::getModel('sales/order')->load($order);
        }

        if (!$order->isCanceled() || $checkCanceled) {
            $orderItem = Mage::helper('rockar_checkout/order')->getFirstVisibleItem($order);

            if ($orderItem) {
                $orderItemLeadTime = $orderItem->getLeadTime();
                $leadTimeItem = $orderItemLeadTime ? Mage::helper('core')->jsonDecode($orderItemLeadTime) : [];
                $identifier = $leadTimeItem['identifier'] ?? null;

                if ($identifier) {
                    $coreResource = Mage::getSingleton('core/resource');
                    $readAdapter = $coreResource->getConnection('core_read');
                    $select = $readAdapter->select()
                        ->from($coreResource->getTableName('rockar_lead_time/lead_time'), ['id'])
                        ->where('identifier = ?', $identifier)
                        ->limit(1);

                    $leadTimeId = $readAdapter->fetchOne($select);
                }
            }
        }

        return $leadTimeId ?? null;
    }

    /**
     * Get products with lead times which should be enabled.
     * @todo temporary workaround until super products are in scope.
     * Inner join with super table was removed.
     * @param boolean $checkVisibleIn
     * @return array
     */
    protected function _getSimpleProducts($checkVisibleIn = false)
    {
        $leadTimeHelper = Mage::helper('rockar_lead_time');
        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');

        /**
         * Select products which should be enabled.
         */
        $endOfLineAttribute = Mage::getModel('eav/config')->getAttribute(
            Mage_Catalog_Model_Product::ENTITY,
            'end_of_line'
        );

        $enabledSelect = $readConnection->select()
            ->distinct(true)
            ->from(['product' => $resource->getTableName('catalog/product')], ['product_id' => 'entity_id', 'sku'])
            ->joinLeft(
                ['lead_time' => $resource->getTableName('rockar_lead_time/lead_time')],
                'product.sku LIKE CONCAT(lead_time.identifier, "%") AND lead_time.amount > 0 ',
                ['lead_time_exists' => 'NOT ISNULL(lead_time.id)']
            )->joinLeft(
                ['end_of_line' => $endOfLineAttribute->getBackendTable()],
                'end_of_line.entity_id = product.entity_id AND end_of_line.attribute_id = ' . $endOfLineAttribute->getId(),
                ['is_end_of_line' => 'end_of_line.value']
            )->joinLeft(
                ['youdrive_vehicle' => $resource->getTableName('rockar_youdrive/vehicle')],
                'youdrive_vehicle.product_id = product.entity_id',
                ['youdrive_vehicle_exists' => 'NOT ISNULL(youdrive_vehicle.id)']
            )->where('product.type_id = "' . Mage_Catalog_Model_Product_Type::TYPE_SIMPLE . '"');

        // Product should be enabled if it has either lead time or youdrive vehicle or both
        $enabledProductQuery = [
            'lead_time_exists = 1',
            'youdrive_vehicle_exists = 1'
        ];

        // Product should be visible only in CATALOG if it has valid lead time, but doesn't have youdrive vehicle
        $visibleInCatalogProductQuery = ['(lead_time_exists = 1 AND youdrive_vehicle_exists = 0)'];

        // Product should be visible only in YOUDRIVE if it has no valid lead time, but does have youdrive vehicle
        $visibleInYoudriveProductQuery = ['(lead_time_exists = 0 AND youdrive_vehicle_exists = 1)'];

        // Product should be visible in both CATALOG and YOUDRIVE if it has valid lead time and youdrive vehicle
        $visibleInCatalogYoudriveProductQuery = ['(lead_time_exists = 1 AND youdrive_vehicle_exists = 1)'];

        // Check product scope
        if ($leadTimeHelper->checkIfLeadTimeScopeEnabled(Rockar_LeadTime_Helper_Data::LEAD_TIME_SCOPE_PRODUCT)) {
            $leadTimeAttribute = Mage::getModel('eav/config')->getAttribute(
                Mage_Catalog_Model_Product::ENTITY,
                'default_lead_time'
            );

            $enabledSelect->joinLeft(
                ['product_lead_time' => $leadTimeAttribute->getBackendTable()],
                'product_lead_time.value > 0 AND (end_of_line.value = 0 OR ISNULL(end_of_line.value)) AND product_lead_time.attribute_id = ' . $leadTimeAttribute->getId(),
                ['product_lead_time_exists' => 'NOT ISNULL(product_lead_time.value_id)']
            );

            $enabledProductQuery[] = 'product_lead_time_exists = 1';

            if ($checkVisibleIn) {
                // Product should be visible only in CATALOG if it has valid product lead time, but doesn't have youdrive vehicle
                $visibleInCatalogProductQuery[] = ('(product_lead_time_exists = 1 AND youdrive_vehicle_exists = 0)');

                // Product should be visible only in YOUDRIVE if it has no valid product lead time, but does have youdrive vehicle
                $visibleInYoudriveProductQuery[] = ('(product_lead_time_exists = 0 AND youdrive_vehicle_exists = 1)');

                // Product should be visible in both CATALOG and YOUDRIVE if it has valid product lead time and youdrive vehicle
                $visibleInCatalogYoudriveProductQuery[] = ('(product_lead_time_exists = 1 AND youdrive_vehicle_exists = 1)');
            }
        }

        // Check default model scope
        if ($leadTimeHelper->checkIfLeadTimeScopeEnabled(Rockar_LeadTime_Helper_Data::LEAD_TIME_SCOPE_DEFAULT_MODEL)) {
            $modelAttribute = Mage::getModel('eav/config')->getAttribute(
                Mage_Catalog_Model_Product::ENTITY,
                $leadTimeHelper->getModelAttribute()
            );

            $enabledSelect->join(
                ['model' => $modelAttribute->getBackendTable()],
                'model.entity_id = product.entity_id && model.attribute_id = ' . $modelAttribute->getId(),
                ['']
            )->joinLeft(
                ['model_lead_time' => $resource->getTableName('rockar_lead_time/default_lead_time')],
                'FIND_IN_SET(model.value, model_lead_time.models) AND model_lead_time.weeks > 0 AND (end_of_line.value = 0 OR ISNULL(end_of_line.value))',
                ['model_lead_time_exists' => 'NOT ISNULL(model_lead_time.id)']
            );

            $enabledProductQuery[] = 'model_lead_time_exists = 1';

            if ($checkVisibleIn) {
                // Product should be visible only in CATALOG if it has valid model lead time, but doesn't have youdrive vehicle
                $visibleInCatalogProductQuery[] = ('(model_lead_time_exists = 1 AND youdrive_vehicle_exists = 0)');

                // Product should be visible only in YOUDRIVE if it has no valid model lead time, but does have youdrive vehicle
                $visibleInYoudriveProductQuery[] = ('(model_lead_time_exists = 0 AND youdrive_vehicle_exists = 1)');

                // Product should be visible in both CATALOG and YOUDRIVE if it has valid model lead time and youdrive vehicle
                $visibleInCatalogYoudriveProductQuery[] = ('(model_lead_time_exists = 1 AND youdrive_vehicle_exists = 1)');
            }
        }

        // Check default fallback
        if ($leadTimeHelper->checkIfLeadTimeScopeEnabled(Rockar_LeadTime_Helper_Data::LEAD_TIME_SCOPE_DEFAULT_FALLBACK)) {
            // default fallback enabled, everything is valid as long as no end of line for product
            $enabledProductQuery[] = '1 = 1 AND (end_of_line.value = 0 OR ISNULL(end_of_line.value))';

            if ($checkVisibleIn) {
                // Product should be visible only in CATALOG if it is not end_of_line product and doesn't have youdrive vehicle
                $visibleInCatalogProductQuery[] = ('((1 = 1 AND (end_of_line.value = 0 OR ISNULL(end_of_line.value))) AND youdrive_vehicle_exists = 0)');

                // Product should be visible only in YOUDRIVE if it is end_of_line product and does have youdrive vehicle
                $visibleInYoudriveProductQuery[] = ('(NOT (1 = 1 AND (end_of_line.value = 0 OR ISNULL(end_of_line.value))) AND youdrive_vehicle_exists = 1)');

                // Product should be visible in both CATALOG and YOUDRIVE if it is not end_of_line product and has youdrive vehicle
                $visibleInCatalogYoudriveProductQuery[] = ('((1 = 1 AND (end_of_line.value = 0 OR ISNULL(end_of_line.value))) AND youdrive_vehicle_exists = 1)');
            }
        }

        if (!empty($enabledProductQuery) && !$checkVisibleIn) {
            $enabledSelect->having(implode(' OR ', $enabledProductQuery));
        } else if ($checkVisibleIn) {
            // If visible in status passed as argument, apply corresponding visibility query
            switch ($checkVisibleIn) {
                case Rockar_YouDrive_Model_Product_Attribute_Source_Visiblein::CATALOG:
                    $enabledSelect->having(implode(' OR ', $visibleInCatalogProductQuery));
                    break;
                case Rockar_YouDrive_Model_Product_Attribute_Source_Visiblein::YOUDRIVE:
                    $enabledSelect->having(implode(' OR ', $visibleInYoudriveProductQuery));
                    break;
                case Rockar_YouDrive_Model_Product_Attribute_Source_Visiblein::CATALOG_AND_YOUDRIVE:
                    $enabledSelect->having(implode(' OR ', $visibleInCatalogYoudriveProductQuery));
                    break;
                default:
                    // skip others
                    break;
            }
        }

        $enabledProductIds = $readConnection->fetchAll($enabledSelect);
        // Adding dummy id to prevent empty IN sql statement resulting in error
        $enabledProductIds[] = ['product_id' => 0];

        return is_array($enabledProductIds)
            ? array_column($enabledProductIds, 'product_id')
            : [];
    }

    /**
     * increase the available quantity of a product when order cancelled
     *
     * @param  Varien_Event_Observer $observer
     *
     * @return void
     */
    public function increaseLeadTimeAmountOrderCancel(Varien_Event_Observer $observer)
    {
        Mage::helper('peppermint_leadtime/order')->increaseOrderProductLeadTimeAmount($observer->getEvent()->getOrder());
    }

    /**
     * increase the available quantity of a product when order refunded
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function increaseLeadTimeAmountOrderRefund(Varien_Event_Observer $observer)
    {
        $creditMemo = $observer->getCreditmemo();
        $order = $creditMemo ? $creditMemo->getOrder() : null;

        if ($creditMemo
            && $order
            && $creditMemo->getState() === Mage_Sales_Model_Order_Creditmemo::STATE_REFUNDED
            && $order->getState() === $order::STATE_CLOSED
        ) {
            Mage::helper('peppermint_leadtime/order')->increaseOrderProductLeadTimeAmount($order);
        }
    }
}

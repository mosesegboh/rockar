<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Orderstatus
 * @author    Mihai Chezan <mihai.chezan@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Orderstatus_Helper_Data extends Rockar_Orderstatus_Helper_Data
{
    /**
     * Admin order status value column name in sales_flat_order
     * @var string
     */
    const ADMIN_ORDER_STATUS_SALES_FLAT_COL_NAME = 'admin_order_status';

    /**
     * Admin order status mapping, [ entity_id => position ]
     * @var null|array
     */
    protected $_adminOrderStatusMapping;

    /**
     * @var string ACL_PATH_PEPPERMINT_ORDER_STATUS_MAPPING
     * Path for the ACL permission for whether BE user is allowed to access peppermint order status mapping
     */
    const ACL_PATH_PEPPERMINT_ORDER_STATUS_MAPPING = 'admin/sales/peppermint_order_status';

    /**
     *  Return admin internal user order status.
     *
     * @return array
     */
    public function getOrderAdminStatuses(): array
    {
        $customerStatuses = Mage::getModel('rockar_orderstatus/status')->getCollection()
            ->addFieldToSelect(['label', 'position'])
            ->setOrder('position', 'ASC');

        $statuses = [];

        foreach ($customerStatuses as ['position' => $position, 'label' => $label]) {
            $statuses[$position] = $label;
        }

        return $statuses;
    }

    /**
     * Save customer facing order status on order place
     *
     * @param Mage_Sales_Model_Order $order
     * @param string $customerFacingStatusId
     *
     * @throws Mage_Core_Exception
     *
     * @return void
     */
    public function orderPlaceSaveCustomerOrderStatus(Mage_Sales_Model_Order $order, $customerFacingStatusId)
    {
        try {
            $orderId = $order->getId() ?: Mage::getModel('sales/order')->loadByIncrementId($order->getIncrementId())
                ->getId();

            if ($orderId) {
                Mage::getModel('rockar_orderstatus/order')->load($orderId, 'order_id')
                    ->addData([
                        'orderstatus_id' => $customerFacingStatusId,
                        'order_id' => $orderId,
                        'created_at' => Mage::getModel('core/date')->date()
                    ])
                    ->save();
            }
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::throwException($this->__('Sorry, there was a problem processing your order, please try again.'));
        }
    }

    /**
     * Save new relation for order customer status.
     *
     * @param Mage_Sales_Model_Order $order
     * @param int $statusId
     * @return Mage_Sales_Model_Order
     * @throws Exception
     */
    public function updateOrderCustomerStatus(Mage_Sales_Model_Order $order, $statusId): Mage_Sales_Model_Order
    {
        /** @var Rockar_Orderstatus_Model_Status $statusModel */
        $statusModel = Mage::getModel('rockar_orderstatus/status')->load($statusId);

        if ($statusModel->getId()) {
            /**
             * @var $orderStatusModel Rockar_Orderstatus_Model_Order
             */
            $orderStatusModel = Mage::getModel('rockar_orderstatus/order')->setOrderstatusId($statusId)
                ->setOrderId($order->getId())
                ->setCreatedAt(Mage::getModel('core/date')->date());

            $orderStatusModel->save();
        }

        return $order;
    }

    /**
     * Update order admin status field on order
     *
     * @param Mage_Sales_Model_Order $order
     * @param string $statusId
     * @return Void
     */
    public function updateOrderAdminStatusRaw(Mage_Sales_Model_Order $order, $statusId): void
    {
        if ($statusId !== null && $order->getId()) {
            $coreResource = Mage::getSingleton('core/resource');
            $writeConnection = $coreResource->getConnection('core_write');

            try {
                $writeConnection->update(
                    $coreResource->getTableName('sales/order'),
                    [self::ADMIN_ORDER_STATUS_SALES_FLAT_COL_NAME => $statusId],
                    $writeConnection->quoteInto("{$order->getIdFieldName()} = ?", $order->getId())
                );
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }

    /**
     * Get Admin order status mapping
     *
     * @param bool $idAsKeys
     * @return array
     */
    public function getAdminOrderStautsMapping($idAsKeys = true): array
    {
        if (!$this->_adminOrderStatusMapping) {
            $this->_adminOrderStatusMapping = [];

            foreach (Mage::getModel('rockar_orderstatus/status')->getCollection()
                ->addFieldToSelect(['entity_id', 'position']) as $orderStatus
            ) {
                $this->_adminOrderStatusMapping[$orderStatus->getEntityId()] = $orderStatus->getPosition();
            }
        }

        return $idAsKeys ? $this->_adminOrderStatusMapping : array_flip($this->_adminOrderStatusMapping);
    }

    /**
     * Get Admin order status mapping
     *
     * @param bool $idAsKeys
     * @return array
     */
    public function getAdminOrderStautsEntityId($idAsKeys = true): array
    {
        if (!$this->_adminOrderStatusMapping) {
            $this->_adminOrderStatusMapping = [];

            foreach (Mage::getModel('rockar_orderstatus/status')->getCollection()
                ->addFieldToSelect(['entity_id', 'position']) as $orderStatus
            ) {
                $this->_adminOrderStatusMapping[$orderStatus->getEntityId()] = $orderStatus->getPosition();
            }
        }

        return $idAsKeys ? $this->_adminOrderStatusMapping : array_flip($this->_adminOrderStatusMapping);
    }

    /**
     * Update admin and customer orderstatus update base on peppermint mapping
     *
     * @param Mage_Sales_Model_Order $order
     * @param bool $saveAdminOrderStatus
     *
     * @return Void
     */
    public function orderStatusMappingUpdate($order, $saveAdminOrderStatus = true)
    {
        if ($order) {
            $rockarStatusTableName = Mage::getSingleton('core/resource')->getTableName('rockar_orderstatus/status');

            /** @var Peppermint_Orderstatus_Model_Resource_Status_Mapping_Collection $collection */
            $collection = Mage::getModel('peppermint_orderstatus/status_mapping')->getCollection()
                ->removeAllFieldsFromSelect()
                ->addFieldToFilter('order_status', $order->getStatus())
                ->setCurPage(1)
                ->setPageSize(1);

            $collection->getSelect()
                ->join(
                    ['ros' => $rockarStatusTableName],
                    'ros.entity_id = main_table.orderstatus_id',
                    ['orderstatus_id' => 'ros.entity_id']
                );

            $orderStatusMappingId = $collection->getFirstItem()
                ->getOrderstatusId();

            $orderId = $order->getId();
        }

        if ($orderStatusMappingId ?? false) {
            if (Mage::registry('order_place')) {
                $this->orderPlaceSaveCustomerOrderStatus($order, $orderStatusMappingId);
            } else {
                Mage::getModel('rockar_orderstatus/order')->load($orderId, 'order_id')
                    ->addData([
                        'orderstatus_id' => $orderStatusMappingId,
                        'order_id' => $orderId,
                        'created_at' => Mage::getModel('core/date')->date()
                    ])
                    ->save();
            }

            // Sync admin status with FE order status
            if (
                ($adminOrderStatusId =
                    ($this->getAdminOrderStautsMapping()[$orderStatusMappingId] ?? false)
                )
            ) {
                if ($saveAdminOrderStatus) {
                    $this->updateOrderAdminStatusRaw($order, $adminOrderStatusId);
                } else {
                    $this->updateOrderAdminStatus($order, $adminOrderStatusId);
                }
            }
        }
    }

    /**
     * Rockar order status options hash array getter
     *
     * @return array
     */
    public function getRockarOrderStatusHashArray(): array
    {
        $options = [];

        $statuses = Mage::getModel('rockar_orderstatus/status')->getCollection()
            ->addFieldToSelect(['entity_id', 'label']);

        foreach ($statuses as $status) {
            $options[$status->getId()] = $this->__($status->getLabel());
        }

        return $options;
    }

    /**
     * Get Acl Path for Peppermint order status mapping
     * @return string
     */
    public function getAclPathOrderStatusMapping(): string
    {
        return self::ACL_PATH_PEPPERMINT_ORDER_STATUS_MAPPING;
    }
}

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Orderamend
 * @author    Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Orderamend_Helper_Order extends Rockar_Orderamend_Helper_Order
{
    /**
     * Accept amendment. Amendment order will be un-holded. Previous order is canceled.
     *
     * @param Mage_Sales_Model_Order $amendmentOrder
     * @param Mage_Sales_Model_Order $originalOrder
     * @return boolean
     */
    protected function _acceptAmendment(Mage_Sales_Model_Order $amendmentOrder, Mage_Sales_Model_Order $originalOrder)
    {
        /** @var Rockar_Orderstatus_Helper_Data $orderStatusHelper */
        $orderStatusHelper = Mage::helper('rockar_orderstatus');

        $previousCustomerStatus = $amendmentOrder->getPreviousCustomerOrderStatus();
        $previousAdminStatus = $amendmentOrder->getPreviousAdminOrderStatus();

        try {
            // Unhold amendment & set order status if according config value selected
            if ($amendmentOrder->canUnhold()) {
                $amendmentOrder->unhold();

                $acceptedAmendmentOrderLogic = $orderStatusHelper->getAcceptedAmendmentOrderLogicId();
                $acceptedAmendmentOrderStatus = $orderStatusHelper->getAcceptedAmendmentOrderStatus();

                if (($acceptedAmendmentOrderLogic === $orderStatusHelper->getOrderAcceptedAmendmentApplySpecificStatus())
                    && $acceptedAmendmentOrderStatus
                ) {
                    $amendmentOrder->setState($amendmentOrder::STATE_PROCESSING, $acceptedAmendmentOrderStatus);
                }
            }

            // Cancel original order, must unhold first
            if ($originalOrder->canUnhold()) {
                $this->unholdWithoutHistory($originalOrder);

                $this->_prepareInvoicedItemsForCancellation($originalOrder);

                $originalOrder->setStatus(Peppermint_Sales_Model_Order::STATUS_AMENDMENT_CANCELLED);

                if ($originalOrder->canCancel()) {
                    $this->_cancelOrder($originalOrder);
                    $adminOrderStatusId = $orderStatusHelper->getCancelledOrderStatusId();
                    // Update original order BE status to cancelled
                    $originalOrder = $orderStatusHelper->updateOrderAdminStatus(
                        $originalOrder,
                        $adminOrderStatusId
                    );

                    // Sync admin status with FE order status
                    if (
                        ($orderStatusMappingId =
                            (Mage::helper('peppermint_orderstatus')->getAdminOrderStautsMapping(false)[$adminOrderStatusId] ?? false)
                        )
                    ) {
                        $orderStatusHelper->updateOrderCustomerStatus($originalOrder, $orderStatusMappingId);
                    }
                }
            }

            $this->updateCommunication($originalOrder, $amendmentOrder, Rockar_Orderamend_Helper_Communication::AMEND_COMMUNICATION_ACCEPTED);
            // Save orders
            $amendmentOrder->save();
            $originalOrder->save();
        } catch (Exception $e) {
            Mage::logException($e);

            return false;
        }

        Mage::dispatchEvent(
            'peppermint_order_amendment_accepted',
            [
                'amendment_order' => $amendmentOrder,
                'original_order' => $originalOrder
            ]
        );

        return true;
    }

    /**
     * Reject amendment. Previous order will be un-holded. Amendment order is canceled.
     *
     * @param Mage_Sales_Model_Order $amendmentOrder
     * @param Mage_Sales_Model_Order $originalOrder
     * @return boolean
     */
    protected function _rejectAmendment(Mage_Sales_Model_Order $amendmentOrder, Mage_Sales_Model_Order $originalOrder)
    {
        /** @var Rockar_Orderstatus_Helper_Data $orderStatusHelper */
        $orderStatusHelper = Mage::helper('rockar_orderstatus');

        $previousCustomerStatus = $originalOrder->getPreviousCustomerOrderStatus();
        $previousAdminStatus = $originalOrder->getPreviousAdminOrderStatus();

        try {
            // Update original order statuses to what they were previously
            $originalOrder = $orderStatusHelper->updateOrderCustomerStatus($originalOrder, $previousCustomerStatus);
            $originalOrder = $orderStatusHelper->updateOrderAdminStatus($originalOrder, $previousAdminStatus);

            // Unhold original order
            if ($originalOrder->canUnhold()) {
                $originalOrder->unhold();
            }

            // Cancel amendment order, must unhold first
            if ($amendmentOrder->canUnhold()) {
                $this->unholdWithoutHistory($amendmentOrder);

                $this->_prepareInvoicedItemsForCancellation($amendmentOrder);

                $amendmentOrder->setStatus(Peppermint_Sales_Model_Order::STATUS_AMENDMENT_CANCELLED);

                if ($amendmentOrder->canCancel()) {
                    $this->_cancelOrder($amendmentOrder);
                    $adminOrderStatusId = $orderStatusHelper->getCancelledOrderStatusId();

                    // Update amendment order BE status to cancelled
                    $orderStatusHelper->updateOrderAdminStatus(
                        $amendmentOrder,
                        $adminOrderStatusId
                    );

                    // Sync admin status with FE order status
                    if (
                        ($orderStatusMappingId =
                            (Mage::helper('peppermint_orderstatus')->getAdminOrderStautsMapping(false)[$adminOrderStatusId] ?? false)
                        )
                    ) {
                        $orderStatusHelper->updateOrderCustomerStatus($amendmentOrder, $orderStatusMappingId);
                    }
                }
            }

            $this->updateCommunication($originalOrder, $amendmentOrder, Rockar_Orderamend_Helper_Communication::AMEND_COMMUNICATION_REJECTED);

            $originalOrder->save();
            $amendmentOrder->save();
        } catch (Exception $e) {
            Mage::logException($e);

            return false;
        }

        Mage::dispatchEvent(
            'peppermint_order_amendment_rejected',
            [
                'amendment_order' => $amendmentOrder,
                'original_order' => $originalOrder
            ]
        );

        return true;
    }

    /**
     * Cancel order - don't send cancel email
     *
     * @param Mage_Sales_Model_Order $order
     * @throws Mage_Core_Exception
     * @return void
     */
    protected function _cancelOrder(Mage_Sales_Model_Order $order): void
    {
        Mage::register('no_cancel_email', true, true);
        $order->cancel();
        Mage::unregister('no_cancel_email');
    }

    /**
     * Check if given product is same that was ordered
     *
     * @param Mage_Catalog_Model_Product|Rockar_Orderamend_Model_Decorators_PriceOverride_Product $product
     * @param Mage_Sales_Model_Order_Item|null $orderItem
     * @return bool
     */
    public function isOrderItemProduct($product, $orderItem = null)
    {
        /** @var Rockar_Orderamend_Helper_Data $helper */
        $helper = Mage::helper('rockar_orderamend');

        if (!$orderItem) {
            $orderItem = $helper->getFirstVisibleOrderItem();
        }
        //if simple orderItem exist use that since the we are checking out with simple not configurable
        $orderItem = $helper->getFirstSimpleOrderItem() ?: $orderItem;

        // Have to compare by ID for now, since SKU is broken for quote/order items
        return $orderItem && $orderItem->getId() && $product->getId() === $orderItem->getProductId();
    }

    /**
     * Compare product of new order to previous order
     * and return true if product is the same
     *
     * @param Mage_Sales_Model_Order $newOrder
     * @return bool
     */
    public function isProductSame($newOrder)
    {
        $previousOrder = $this->_getOriginalOrder($newOrder);
        $checkoutHelper = Mage::helper('rockar_checkout/order');

        if ($newOrder && $previousOrder) {
            if ($checkoutHelper->getFirstVisibleItem($newOrder)->getProductId() ===
                $checkoutHelper->getFirstVisibleItem($previousOrder)->getProductId()) {

                return true;
            }
        }

        return false;
    }

    /**
     * Get previous order if it exists
     *
     * @param Mage_Sales_Model_Order $order
     * @return bool|Mage_Core_Model_Abstract
     */
    public function getPreviousOrder($order)
    {
        return $order ? $this->_getOriginalOrder($order) : false;
    }

    /**
     * Check whether given order is pending amendment
     * Rewrite, remove BE admin status check as it is not being use in PEP
     *
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    public function isPendingAmendment(Mage_Sales_Model_Order $order)
    {
        /** @var Rockar_Orderstatus_Helper_Data $orderStatusHelper */
        $orderStatusHelper = Mage::helper('rockar_orderstatus');

        return (
            $orderStatusHelper->getOrderStatusIdByType($order, 'customer')
            === $orderStatusHelper->getAwaitingAmendmentOrderStatusId('customer')
            && $order->canUnhold()
            && !$order->getRelationChildId()
        );
    }

    /**
     * Unhold an order without writing to history, to avoid
     * multiple entries if other actions like state change or cancel
     * are done right after.
     *
     * @param Mage_Sales_Model_Order $order
     * @return Mage_Sales_Model_Order
     * @throws Mage_Core_Exception
     */
    public function unholdWithoutHistory(Mage_Sales_Model_Order $order)
    {
        if (!$order->canUnhold()) {
            Mage::throwException(Mage::helper('sales')->__('Unhold action is not available.'));
        }

        $order->addData([
            'state' => $order->getHoldBeforeState(),
            'status' => $order->getHoldBeforeStatus()
        ]);

        $order->setHoldBeforeState(null);
        $order->setHoldBeforeStatus(null);

        return $order;
    }
}

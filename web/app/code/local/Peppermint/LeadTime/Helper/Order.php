<?php
/**
 * @category  Peppermint
 * @package   Peppermint_LeadTime
 * @author    Osama Ahmed <osama.ahmed@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_LeadTime_Helper_Order extends Mage_Core_Helper_Abstract
{
    /**
     * Increase the available quantity of a product
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return null|void
     */
    public function increaseOrderProductLeadTimeAmount(Mage_Sales_Model_Order $order)
    {
        if (!$this->isOrderProductValid($order)) {
            return;
        }

        $leadTimeId = $this->getOrderLeadTimeId($order, true);

        // don't increase amount if the order has an amendment for the same lead time
        // don't increase amount if the order is an amendment for the same lead time
        if (!empty($leadTimeId)
            && $this->getOrderLeadTimeId($order->getRelationChildId()) !== $leadTimeId
            && $this->getOrderLeadTimeId($order->getRelationParentId()) !== $leadTimeId
        ) {
            $productLeadTime = Mage::getModel('rockar_lead_time/lead_time')->getCollection()
                ->addFieldToFilter('id', $leadTimeId)
                ->setPageSize(1)
                ->setCurPage(1)
                ->getFirstItem();

            Mage::register('peppermint_single_product_status_update', true, true);
            $productLeadTime->setAmount(1)
                ->save();
            Mage::unregister('peppermint_single_product_status_update');

            $this->_validateLeadTimeAmount($leadTimeId);
        }
    }

    /**
     * Checks to confirm the product associated with the order is valid
     *
     * @param  Peppermint_Sales_Model_Order|string $orderId
     * @return boolean
     */
    public function isOrderProductValid($orderId)
    {
        if (!$orderId) {
            return false;
        }

        if ($orderId instanceof Mage_Sales_Model_Order) {
            $orderId = $orderId->getId();
        }

        if (!is_numeric($orderId)) {
            return false;
        }

        $readAdapter = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = $readAdapter->select()
            ->from(Mage::getSingleton('core/resource')->getTableName('sales/order_item'), ['sku'])
            ->where('order_id = ?', $orderId)
            ->where('product_type = ?', Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
            ->limit(1);

        $sku = $readAdapter->fetchOne($select);

        if (empty($sku)) {
            return false;
        }

        $sku = strtok($sku, '-');
        $productId = Mage::getModel('catalog/product')->getIdBySku($sku);

        return is_numeric($productId);
    }

    /**
     * Returns lead time id by order id
     *
     * @param Peppermint_Sales_Model_Order|int $order
     *
     * @param bool $checkCanceled
     * @return string|null|false
     */
    public function getOrderLeadTimeId($order, $checkCanceled = false)
    {
        if (!$order) {
            return null;
        }

        if (is_numeric($order)) {
            $order = Mage::getModel('sales/order')->load($order);
        }

        // Need to check both not cancelled or closed state as order can be either
        // After cancellation depending if it was already pre-invoice (invoice submitted status)
        // canceled state => before pre-invoice, closed => after pre-invoice
        if ((!$order->isCanceled() && $order->getState() !== $order::STATE_CLOSED) || $checkCanceled) {
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
     * Validate if leadtime amount has been updated correctly, if not throw error forward
     *
     * @param int $leadTimeId
     *
     * @return void
     * @throws Mage_Core_Exception
     */
    protected function _validateLeadTimeAmount($leadTimeId)
    {
        $resource = Mage::getSingleton('core/resource');

        if (
            $resource->getConnection('core_read')->fetchOne(
                "SELECT amount FROM {$resource->getTableName('rockar_lead_time/lead_time')} WHERE id = '{$leadTimeId}'"
            ) !== '1'
        ) {
            Mage::throwException($this->__('There was an issue updating product availability | Please try again'));
        }
    }
}

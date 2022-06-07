<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Importer
 * @author    Osama Ahmed <osama.ahmed@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Importer_Helper_Order extends Mage_Core_Helper_Abstract
{
    /**
     * Invoice and ship an order
     *
     * @param Peppermint_Sales_Model_Order|false $orderObject
     * @param array $vinArray
     * @return bool|void
     */
    public function invoiceAndShipOrder($vinArray, $orderObject = false)
    {
        $orders = $orderObject
            ? [$orderObject]
            : Mage::getModel('sales/order')->getCollection()
                ->addAttributeToFilter('vin_number', ['in' => $vinArray])
                ->addFieldToFilter('status', Peppermint_Sales_Model_Order::STATUS_INVOICE_SUBMITTED);

        $salesInvoiceModel = Mage::getModel('sales/order_invoice');
        $salesShipmentModel = Mage::getModel('sales/order_shipment');

        foreach ($orders as $order) {
            if ($order && $order->getId()) {
                try {
                    $invoicedOrderCount = $salesInvoiceModel->getCollection()
                        ->addAttributeToFilter('order_id', $order->getId())
                        ->getSize();

                    if ((int) $invoicedOrderCount !== 0) {
                        $shippedOrderCount = $salesShipmentModel->getCollection()
                            ->addAttributeToFilter('order_id', $order->getId())
                            ->getSize();

                        if ((int) $shippedOrderCount === 0) {
                            $this->_transact(false, $this->_shipOrder($order), true);
                            if (Mage::getSingleton('admin/session')->isLoggedIn()) {
                                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The shipment has been created.'));
                            }
                        }

                        return true;
                    }

                    if (!$order->canInvoice()) {
                        $order->addStatusHistoryComment('Order cannot be invoiced.', false)
                            ->save();

                        return false;
                    }

                    $this->_transact($this->_invoiceOrder($order), $this->_shipOrder($order));

                    if (Mage::getSingleton('admin/session')->isLoggedIn()) {
                        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The invoice and shipment have been created.'));
                    }
                } catch (Exception $e) {
                    $order->addStatusHistoryComment('There was an issue when inovicing and shipping this order', false)
                        ->save();

                    Mage::logException($e);
                }
            }
        }
    }

    /**
     * Invoice an order
     *
     * @param Peppermint_Sales_Model_Order $order
     * @return Mage_Sales_Model_Order_Invoice
     */
    protected function _invoiceOrder($order)
    {
        $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice()
            ->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE)
            ->register();

        $invoice->getOrder()
            ->setCustomerNoteNotify(false)
            ->setIsInProcess(true)
            ->addStatusHistoryComment('Order Invoiced', false);

        return $invoice;
    }

    /**
     * Ship an order
     *
     * @param Peppermint_Sales_Model_Order $order
     * @return Mage_Sales_Model_Order_Shipment
     */
    protected function _shipOrder($order)
    {
        $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment()
            ->register();

        $shipment->getOrder()
            ->setCustomerNoteNotify(false)
            ->setIsInProcess(true)
            ->addStatusHistoryComment('Order Shipped', false);

        return $shipment;
    }

    /**
     * Carry out invoice or ship transaction
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @param bool $isPartial
     * @return void
     */
    protected function _transact($invoice, $shipment, $isPartial = false)
    {
        $isPartial && !$invoice
            ? Mage::getModel('core/resource_transaction')->addObject($shipment)
                ->addObject($shipment->getOrder())
                ->save()
            : Mage::getModel('core/resource_transaction')->addObject($invoice)
                ->addObject($invoice->getOrder())
                ->addObject($shipment)
                ->save();

        Mage::dispatchEvent('peppermint_order_preretail_after', ['order' => $shipment->getOrder()]);
    }
}

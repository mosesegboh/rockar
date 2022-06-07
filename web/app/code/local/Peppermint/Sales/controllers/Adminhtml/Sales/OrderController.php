<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Taras Kapushchak <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml') . DS . 'Sales/OrderController.php';
class Peppermint_Sales_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController
{
    /**
     * Order Sync to SPC action
     *
     * @return void
     */
    public function syncOrderAction()
    {
        if ($order = $this->_initOrder()) {
            try {
                Mage::helper('peppermint_dfe/submitApp')->sendOrderToDfe($order);
                $this->_getSession()->addSuccess($this->__(
                    'Order information was sent to DFE. Please check comments history for error messages (if any).'
                ));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('The order was not synced. Please try again.'));
            }
            $this->_redirect('*/sales_order/view', ['order_id' => $order->getId()]);
        }
    }

    /**
     * Save data for order and related shipment
     *
     * @return void
     */
    public function preretailAction()
    {
        if ($order = $this->_initOrder()) {
            try {
                Mage::helper('peppermint_importer/order')->invoiceAndShipOrder(false, $order);
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($this->__('There is a problem saving the order. Please try again.'));
            }
            $this->_redirect('*/sales_order/view', ['order_id' => $order->getId()]);
        }
    }

    /**
     * Refresh order product action
     *
     * @return void
     */
    public function refreshProductAction()
    {
        if ($order = $this->_initOrder()) {
            try {
                Mage::helper('peppermint_sales/order')->refreshOrderProductData($order);
                $this->_getSession()->addSuccess($this->__('Order product data has been updated.'));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($this->__('There is a problem refreshing order product data. Please try again.'));
            }

            $this->_redirect('*/sales_order/view', ['order_id' => $order->getId()]);
        }
    }

    /**
     * Send order to SAP
     *
     * @return void
     */
    public function sendOrderAction()
    {
        if ($order = $this->_initOrder()) {
            try {
                Mage::helper('peppermint_sales/order')->prepareAndPushOrderToDs($order);
                $this->_getSession()->addSuccess($this->__('Order successfully sent to SAP.'));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('Order failed to send to SAP.'));
            }

            $this->_redirect('*/sales_order/view', ['order_id' => $order->getId()]);
        }
    }

    /**
     * Cancel order
     *
     * @return void
     */
    public function cancelAction()
    {
        if ($order = $this->_initOrder()) {
            try {
                $order->cancel()
                    ->save();

                $this->_getSession()->addSuccess(
                    $this->__('The order has been cancelled.')
                );

                Mage::dispatchEvent('peppermint_order_cancelled_success', ['order' => $order]);
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('The order has not been cancelled.'));
                Mage::logException($e);
            }

            $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
        }
    }

    /**
     * Invoice message to SAP
     *
     * @return void
     */
    public function prepareInvoiceAction()
    {
        if ($order = $this->_initOrder()) {
            try {
                $order->setStatus($order::STATUS_INVOICE_SUBMITTED)->save();
                Mage::dispatchEvent('sales_order_prepare_invoice_register', ['order' => $order]);

                $this->_getSession()->addSuccess($this->__('Order status successfully changed to invoice submitted!'));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('An error occurred during changing order status to invoice submitted.'));
                Mage::logException($e);
            }

            $this->_redirect('*/sales_order/view', ['order_id' => $order->getId()]);
        }
    }

    /**
     * Order amend lock action
     *
     * @return void
     */
    public function orderAmendLockAction()
    {
        if ($order = $this->_initOrder()) {
            try {
                $helper = Mage::helper('peppermint_sales/order');
                $helper->setLockOrUnlockOrderAmend(
                    [$helper->getOrderVinNumber($order)],
                    Peppermint_Sales_Model_AdditionalData::CAN_AMEND_LOCK
                );

                $this->_getSession()->addSuccess($this->__('Order Amend successfully lock to for this order'));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('An error occurred during order amend locking'));
                Mage::logException($e);
            }

            $this->_redirect('*/sales_order/view', ['order_id' => $order->getId()]);
        }
    }
}

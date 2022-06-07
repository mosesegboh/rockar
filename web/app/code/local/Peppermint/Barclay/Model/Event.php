<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Barclay
 * @author    Aleksejs Oboruns <techteam@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Barclay_Model_Event extends Rockar_Barclay_Model_Event
{
    /**
     * {@inheritdoc}
     * @return Peppermint_Sales_Model_Order
     */
    public function processSuccess()
    {
        $this->validate();

        $order = $this->_getOrder();
        $partExchange = Mage::getModel('rockar_partexchange/order')->getCollection()
            ->addFieldToFilter('order_id', $order->getId())
            ->getFirstItem();

        if ($order->getState() !== $order::STATE_PROCESSING) {
            if ($partExchange->getData()) {
                $order->setState($order::STATE_NEW, true, 'Payment was completed');
            } else {
                $order->setState($order::STATE_PROCESSING, true, 'Payment was completed');
            }
            $order->save();

            Mage::helper('rockar_partialpayment')->completePartialPayment($order->getId());

            if (isset($this->_eventData['PAYID'])) {
                Mage::getSingleton('checkout/session')->setPaymentTransactionId($this->_eventData['PAYID']);
            }

            $this->_dispatchResponseHandlingEvent('success', ['order' => $order]);
        }

        return $order;
    }
}

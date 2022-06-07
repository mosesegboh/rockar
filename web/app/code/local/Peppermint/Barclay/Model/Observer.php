<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Barclay
 * @author    Aleksejs Oboruns <techteam@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Barclay_Model_Observer extends Rockar_Barclay_Model_Observer
{
    /**
     * Handle barclay success response, sends order confirmation to customer.
     *
     * @param  Varien_Event_Observer $observer
     * @return $this
     */
    public function handleBarclaySuccessResponse(Varien_Event_Observer $observer)
    {
        /** @var Peppermint_Sales_Model_Order $order */
        $order = $observer->getOrder();

        if (!$order->getEmailSent() && $order->getState() === $order::STATE_PROCESSING) {
            $order->queueNewOrderEmail();
            $order->setEmailSent(true);
            $order->getResource()->saveAttribute($order, 'email_sent');
        }

        return $this;
    }
}

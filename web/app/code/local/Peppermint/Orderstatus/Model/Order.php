<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Orderstatus
 * @author    Aleksejs Oboruns <techteam@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 *
 * Class Peppermint_Orderstatus_Model_Order.
 */

class Peppermint_Orderstatus_Model_Order extends Rockar_Orderstatus_Model_Order
{
    /**
     * @param $statusUpdateComment
     * @param string $customStatus
     * @return void
     */
    public function sendStatusUpdateNotification($statusUpdateComment, $customStatus = '')
    {
        if (!$orderId = $this->getOrderId()) {
            return;
        }

        /** @var Rockar_Sales_Model_Order $order */
        $order = $this->_getOrder($orderId);

        if (!$order || !$order->getId()) {
            return;
        }

        $orderStatusLabel = $customStatus ?: $this->_getStatusLabelById($this->getOrderstatusId());

        $storeId = $order->getStoreId();
        $mailer = Mage::getModel('core/email_template_mailer');
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($order->getCustomerEmail());

        if ($bccEmail = Mage::getStoreConfig('sales_email/rockar_orderstatus/email_cc', $storeId)) {
            $emailInfo->addBcc($bccEmail);
        }

        $dealerEmailHelper = Mage::helper('peppermint_dealeremail/dealer');

        // add dealers in Bcc if config enabled.
        if ($dealerEmailHelper->isDealerCopyEnabled('rockar_orderstatus', $storeId)) {
            $this->_setBccDealers($emailInfo, $order);
        }

        Mage::dispatchEvent(
            'rockar_orderstatus_email_notification_send',
            [
                'emailInfo' => $emailInfo,
                'type' => 'rockar_orderstatus',
                'localstore' => $dealerEmailHelper->getLocalStore($order->getDealerCode())
            ]
        );

        $mailer->addEmailInfo($emailInfo)
            ->setSender($this->_getSender($storeId))
            ->setStoreId($storeId)
            ->setTemplateId(Mage::getStoreConfig('sales_email/rockar_orderstatus/template', $storeId))
            ->setTemplateParams(array_merge(
                $order->getExtraEmailVariables($order),
                [
                    'order' => $order,
                    'store' => $order->getStore(),
                    'order_status' => $orderStatusLabel,
                    'status_comment' => $statusUpdateComment
                ]
            ));

        $mailer->send();
    }

    /**
     * @param $statusUpdateComment
     * @param string $customStatus
     * @return void
     */
    public function sendCancelNotification($statusUpdateComment, $customStatus = '')
    {
        if (!$orderId = $this->getOrderId()) {
            return;
        }

        /** @var Rockar_Sales_Model_Order $order */
        $order = $this->_getOrder($orderId);

        $partExchange = Mage::getModel('rockar_partexchange/order')->getCollection()
            ->addFieldToFilter('order_id', $order->getId())
            ->getFirstItem();

        if (!$order || !$order->getId() || $partExchange->getData()) {
            return;
        }

        $orderStatusLabel = $customStatus ?: $this->_getStatusLabelById($this->getOrderstatusId());
        $storeId = $order->getStoreId();

        /** @var Mage_Core_Model_Email_Template_Mailer $mailer */
        $mailer = Mage::getModel('core/email_template_mailer');

        /** @var Mage_Core_Model_Email_Info $emailInfo */
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($order->getCustomerEmail(), $order->getCustomerName());

        if ($bccEmail = Mage::getStoreConfig('sales_email/rockar_order_cancel/email_cc', $storeId)) {
            $emailInfo->addBcc($bccEmail, $order->getCustomerName());
        }

        $dealerEmailHelper = Mage::helper('peppermint_dealeremail/dealer');

        // add dealers in Bcc if config enabled.
        if ($dealerEmailHelper->isDealerCopyEnabled('rockar_order_cancel', $storeId)) {
            $this->_setBccDealers($emailInfo, $order);
        }

        Mage::dispatchEvent(
            'rockar_sales_email_notifications_send',
            [
                'emailInfo' => $emailInfo,
                'type' => 'rockar_order_cancel',
                'localstore' => $dealerEmailHelper->getLocalStore($order->getDealerCode())
            ]
        );

        $mailer->addEmailInfo($emailInfo)
            ->setSender([
                'name' => Mage::getStoreConfig('sales_email/rockar_order_cancel/name', $storeId),
                'email' => Mage::getStoreConfig('sales_email/rockar_order_cancel/email', $storeId)
            ])
            ->setStoreId($storeId)
            ->setTemplateId(Mage::getStoreConfig('sales_email/rockar_order_cancel/template', $storeId))
            ->setTemplateParams(array_merge(
                $order->getExtraEmailVariables($order),
                [
                    'order' => $order,
                    'store' => $order->getStore(),
                    'order_status' => $orderStatusLabel,
                    'status_comment' => $statusUpdateComment
                ]
            ));

        $mailer->send();
    }

    /**
     * Set Bcc dealers.
     *
     * @param [Mage_Core_Model_Abstract] $emailInfo
     * @param [Mage_Sales_Model_Order] $order
     * @return void
     */
    protected function _setBccDealers(&$emailInfo, $order)
    {
        $dealerEmailsList = Mage::helper('peppermint_dealeremail/dealer')->getDealerEmails($order->getDealerCode());

        if (!empty($dealerEmailsList)) {
            $dealersEmails = explode(',', $dealerEmailsList);

            foreach ($dealersEmails as $dealerEmail) {
                $emailInfo->addBcc($dealerEmail);
            }
        }
    }
}

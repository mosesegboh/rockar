<?php
/**
 * @category  Peppermint
 * @package   Peppermint_DealerEmail
 * @author    Bogdan Gafitescu <bogdan.gafitescu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_DealerEmail_Helper_Email extends Rockar_Sales_Helper_Email
{
    /**
     * Send email.
     *
     * @param string $email
     * @param string $event
     * @param array $vars
     *
     * @return boolean|void
     */
    public function sendMail($email, $event, $vars)
    {
        if (!$this->isEnabled($email)) {
            return false;
        }

        $dealerEmailHelper = Mage::helper('peppermint_dealeremail/dealer');
        $store = $vars['store'];
        $order = $vars['order'];

        $copyTo = $this->_getEmails($email);
        $copyMethod = $this->getEmailConfig($email, 'copy_method');

        $templateId = $this->getEmailConfig($email, 'email');
        $vars['customer_name'] = $order->getCustomerName();

        /** @var Mage_Core_Model_Email_Template_Mailer $mailer */
        $mailer = Mage::getModel('core/email_template_mailer');

        /** @var Mage_Core_Model_Email_Info $emailInfo */
        $emailInfo = Mage::getModel('core/email_info')->addTo($order->getCustomerEmail(), $vars['customer_name']);

        Mage::dispatchEvent(
            'rockar_sales_email_notifications_send',
            [
                'emailInfo' => $emailInfo,
                'type' => $email,
                'localstore' => $dealerEmailHelper->getLocalStore($order->getDealerCode())
            ]
        );

        if ($copyMethod == 'bcc') {
            foreach ($copyTo as $bccEmail) {
                $emailInfo->addBcc($bccEmail);
            }
        }

        // add dealers in Bcc if config enabled.
        $storeId = $order->getStoreId();

        if (
            ($email == self::EMAIL_COLLECT_REMINDER && $dealerEmailHelper->isDealerCopyEnabled('rockar_collect', $storeId))
            || ($email == self::EMAIL_DELIVERY_REMINDER && $dealerEmailHelper->isDealerCopyEnabled('rockar_delivery', $storeId))
        ) {
            $this->_setBccDealers($emailInfo, $order);
        }

        $mailer->addEmailInfo($emailInfo);

        if ($copyMethod == 'copy') {
            foreach ($copyTo as $ccEmail) {
                $emailInfo->addTo($ccEmail);
                $mailer->addEmailInfo($emailInfo);
            }
        }

        /** @var Mage_Core_Model_Email_Queue $emailQueue */
        $emailQueue = Mage::getModel('core/email_queue')->setEntityId($order->getId())
            ->setEntityType(Rockar_Sales_Model_Order::ENTITY)
            ->setEventType($event)
            ->setIsForceCheck(true);

        $mailer->setSender($this->getEmailConfig($email, 'identity'))
            ->setStoreId($store->getId())
            ->setTemplateId($templateId)
            ->setTemplateParams($vars)
            ->setQueue($emailQueue)
            ->send();
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

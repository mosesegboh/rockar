<?php
/**
 * @category  Peppermint
 * @package   Peppermint_DealerEmail
 * @author    Bogdan Gafitescu <bogdan.gafitescu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 *
 * Class Peppermint_DealerEmail_Model_Booking.
 *
 * @rewrite Rockar_YouDrive_Model_Booking
 */

class Peppermint_DealerEmail_Model_Booking extends Peppermint_YouDrive_Model_Booking
{
    /**
     * Send transactional email.
     *
     * @param $type
     * @param boolean $forceMode
     * @return $this
     */
    protected function _queueEmail($type, $forceMode = false)
    {
        $storeId = $this->getStoreId();

        if (!$this->_getEmailConfiguration($type, 'enabled')) {
            return $this;
        }

        $copyTo = $this->_getEmailConfiguration($type, 'copy_to');

        if ($copyTo) {
            $copyTo = explode(',', $copyTo);
        }
        $copyMethod = $this->_getEmailConfiguration($type, 'copy_method');
        $templateId = $this->_getEmailConfiguration($type, 'template');

        /** @var Mage_Core_Model_Email_Template_Mailer $mailer */
        $mailer = Mage::getModel('core/email_template_mailer');

        /** @var Mage_Core_Model_Email_Info $emailInfo */
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($this->getCustomer()->getEmail(), $this->getCustomer()->getName());

        if ($copyTo && $copyMethod == 'bcc') {
            // Add bcc to customer email
            foreach ($copyTo as $email) {
                $emailInfo->addBcc($email, $email);
            }
        }

        // add dealers in Bcc if config enabled.
        if ($this->_getEmailConfiguration($type, 'send_copy_to_dealer')) {
            $this->getLocalStore(); // to fill this->_localStore

            if ($this->getDealerAddress() !== false && !empty($this->getDealerAddress()->getEmailAddress())) {
                $dealersEmails = explode(',', $this->getDealerAddress()->getEmailAddress());

                foreach ($dealersEmails as $dealerEmail) {
                    $emailInfo->addBcc($dealerEmail, $dealerEmail);
                }
            }
        }

        Mage::dispatchEvent(
            'rockar_youdrive_email_notifications_send',
            [
                'emailInfo' => $emailInfo,
                'type' => $type,
                'localstore' => $this->getLocalStore()
            ]
        );

        $mailer->addEmailInfo($emailInfo);

        // Email copies are sent as separated emails if their copy method is 'copy'
        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $email) {
                $emailInfo = Mage::getModel('core/email_info')->addTo($email);
                $mailer->addEmailInfo($emailInfo);
            }
        }

        // Set all required params and send emails
        $mailer->setSender($this->_getEmailConfiguration($type, 'sender'));
        $mailer->setStoreId($storeId)
            ->setTemplateId($templateId);

        /**
         * Add custom params.
         */
        $bookedOn = $this->getBookedOn()
            ? Mage::getModel('core/date')->date(Varien_Date::DATETIME_PHP_FORMAT, $this->getBookedOn())
            : null;

        $vehiclesData = $this->_prepareVehiclesDataForEmail();

        $vehicles = new Varien_Object($vehiclesData);
        $vehicles->setVehiclesCount(count($vehiclesData));

        $templateParams = [
            'booking' => $this,
            'customer' => $this->getCustomer(),
            'localStore' => $this->getLocalStore(),
            'dealerAddress' => $this->getDealerAddress(),
            'vehicles' => $vehicles,
            'bookedOn' => $bookedOn ?? ''
        ];
        $mailer->setTemplateParams($templateParams);

        /** @var Mage_Core_Model_Email_Queue $emailQueue */
        $emailQueue = Mage::getModel('core/email_queue');
        $emailQueue->setEntityId($this->getId())
            ->setEntityType(self::ENTITY)
            ->setEventType($type)
            ->setIsForceCheck(!$forceMode);

        $mailer->setQueue($emailQueue)->send();

        return $this;
    }
}

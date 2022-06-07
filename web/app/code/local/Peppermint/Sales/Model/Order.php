<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Catalin Lungu <catalin.lungu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Sales_Model_Order extends Rockar_Sales_Model_Order
{
    /**
     * Use these constances from object scope, eg: $order::STATUS.*.
     */
    public const STATUS_ORDER_PLACED = 'order_placed';
    public const STATUS_INVOICE_SUBMITTED = 'invoice_submitted';
    public const STATUS_VEHICLE_IN_TRANSIT = 'vehicle_in_transit';
    public const STATUS_VEHICLE_ARRIVED = 'vehicle_arrived';
    public const STATUS_AMENDMENT_CANCELLED = 'amendment_cancelled';
    public const STATUS_ORDER_COMPLETE = 'order_complete';
    public const STATUS_ORDER_CANCELLED = 'order_cancelled';
    public const STATUS_ORDER_CLOSED = 'order_closed';

    /**
     * Protected member to remember old state.
     *
     * @var null|string
     */
    protected $_oldState;

    /**
     * Protected member to remember old status.
     *
     * @var null|string
     */
    protected $_oldStatus;

    /**
     * Getter for old state.
     *
     * @return string
     */
    public function getOldState()
    {
        return $this->_oldState;
    }

    /**
     * Setter for old state.
     *
     * @param string $oldState
     * @return $this
     */
    public function setOldState($oldState)
    {
        $this->_oldState = $oldState;

        return $this;
    }

    /**
     * Getter for old status.
     *
     * @return string
     */
    public function getOldStatus()
    {
        return $this->_oldStatus;
    }

    /**
     * Setter for old status.
     *
     * @param string $oldStatus
     * @return $this
     */
    public function setOldStatus($oldStatus)
    {
        $this->_oldStatus = $oldStatus;

        return $this;
    }

    /**
     * Send email with order data.
     *
     * @return Peppermint_Sales_Model_Order
     */
    public function sendNewOrderEmail()
    {
        Mage::helper('peppermint_checkout/pdf')->makeOtp($this->getRealOrderId(), $this);
        $this->queueNewOrderEmail(
            true,
            Mage::helper('peppermint_sales/admin')->isAdmin(),
            Mage::getBaseDir('var') . '/orders',
            $this->getRealOrderId() . '.pdf',
            'application/pdf'
        );

        return $this;
    }

    /**
     * {@inheritdoc}
     * @rewrite queueNewOrderEmail
     * @param boolean $forceMode
     * @param boolean $mFest
     * @param string $filePath
     * @param string $fileName
     * @param string $fileType
     *
     * @throws Exception
     * @return Peppermint_Sales_Model_Order
     */
    public function queueNewOrderEmail($forceMode = false, $mFest = false, $filePath = null, $fileName = null, $fileType = null)
    {
        if ($this->getState() !== $this::STATE_NEW) {
            return $this;
        }

        $storeId = $this->getStore()->getId();

        // If it's for mFest send email without any other checks
        if (!$mFest && !Mage::helper('sales')->canSendNewOrderEmail($storeId)) {
            return $this;
        }

        // Get the destination email addresses to send copies to
        $copyTo = $this->_getEmails(self::XML_PATH_EMAIL_COPY_TO);
        $copyMethod = Mage::getStoreConfig(self::XML_PATH_EMAIL_COPY_METHOD, $storeId);

        // Start store emulation process
        /** @var Mage_Core_Model_App_Emulation $appEmulation */
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        try {
            // Retrieve specified view block from appropriate design package (depends on emulated store)
            $paymentBlock = Mage::helper('payment')->getInfoBlock($this->getPayment())
                ->setIsSecureMode(true);
            $paymentBlock->getMethod()
                ->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
        } catch (Exception $exception) {
            // Stop store emulation process
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

            throw $exception;
        }

        // Stop store emulation process
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        // Retrieve corresponding email template id and customer name
        if ($this->getCustomerIsGuest()) {
            $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId);
            $customerName = $this->getBillingAddress()
                ->getName();
        } else {
            $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE, $storeId);
            $customerName = $this->getCustomerName();
        }

        /** @var Mage_Core_Model_Email_Template_Mailer $mailer */
        $mailer = Mage::getModel('core/email_template_mailer');
        /** @var Mage_Core_Model_Email_Info $emailInfo */
        $emailInfo = Mage::getModel('core/email_info')->addTo($this->getCustomerEmail(), $customerName);

        if ($copyTo && $copyMethod == 'bcc') {
            // Add bcc to customer email
            foreach ($copyTo as $email) {
                $emailInfo->addBcc($email);
            }
        }

        $dealerEmailHelper = Mage::helper('peppermint_dealeremail/dealer');

        // add dealers in Bcc if config enabled.
        if ($dealerEmailHelper->isDealerCopyEnabled('order', $storeId)) {
            $dealerEmailsList = $dealerEmailHelper->getDealerEmails($this->getDealerCode());

            if (!empty($dealerEmailsList)) {
                $dealersEmails = explode(',', $dealerEmailsList);

                foreach ($dealersEmails as $dealerEmail) {
                    $emailInfo->addBcc($dealerEmail);
                }
            }
        }

        Mage::dispatchEvent(
            'rockar_orderstatus_email_notification_send',
            [
                'emailInfo' => $emailInfo,
                'type' => 'order',
                'localstore' => $dealerEmailHelper->getLocalStore($this->getDealerCode())
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
        $mailer->setSender(Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId))
            ->setStoreId($storeId)
            ->setTemplateId($templateId);

        /**
         * Add order custom params.
         */
        $templateParams = array_merge(
            $this->_emailExtraVariables($this),
            [
                'order' => $this,
                'billing' => $this->getBillingAddress(),
                'payment_html' => $paymentBlockHtml
            ]
        );
        $mailer->setTemplateParams($templateParams);

        /** @var Mage_Core_Model_Email_Queue $emailQueue */
        $emailQueue = Mage::getModel('core/email_queue')->setEntityId($this->getId())
            ->setEntityType(self::ENTITY)
            ->setEventType(self::EMAIL_EVENT_NAME_NEW_ORDER)
            ->setIsForceCheck(!$forceMode);

        $mailer->setQueue($emailQueue)
            ->send($filePath, $fileName, $fileType);
        $this->setEmailSent(true);
        $this->_getResource()
            ->saveAttribute($this, 'email_sent');

        return $this;
    }

    /**
     * Overwrite parent function to set canceled and closed states
     *
     * {@inheritDoc}
     */
    public function registerCancellation($comment = '', $graceful = true)
    {
        if ($this->canCancel() || $this->isPaymentReview()) {
            $cancelState = self::STATE_CLOSED;

            $state = $this->getState();

            // set state to cancelled if state is before Invoice Submitted
            if (
                ($state === self::STATE_PROCESSING && $this->getStatus() !== self::STATUS_INVOICE_SUBMITTED)
                || $state === self::STATE_NEW
                || $state === self::STATE_HOLDED
            ) {
                $cancelState = self::STATE_CANCELED;
            }

            foreach ($this->getAllItems() as $item) {
                if ($cancelState !== self::STATE_PROCESSING && $item->getQtyToRefund()) {
                    if ($item->getQtyToShip() > $item->getQtyToCancel()) {
                        $cancelState = self::STATE_PROCESSING;
                    } else {
                        $cancelState = self::STATE_COMPLETE;
                    }
                }
                $item->cancel();
            }

            $this->setSubtotalCanceled($this->getSubtotal() - $this->getSubtotalInvoiced());
            $this->setBaseSubtotalCanceled($this->getBaseSubtotal() - $this->getBaseSubtotalInvoiced());

            $this->setTaxCanceled($this->getTaxAmount() - $this->getTaxInvoiced());
            $this->setBaseTaxCanceled($this->getBaseTaxAmount() - $this->getBaseTaxInvoiced());

            $this->setShippingCanceled($this->getShippingAmount() - $this->getShippingInvoiced());
            $this->setBaseShippingCanceled($this->getBaseShippingAmount() - $this->getBaseShippingInvoiced());

            $this->setDiscountCanceled(abs($this->getDiscountAmount()) - $this->getDiscountInvoiced());
            $this->setBaseDiscountCanceled(abs($this->getBaseDiscountAmount()) - $this->getBaseDiscountInvoiced());

            $this->setTotalCanceled($this->getGrandTotal() - $this->getTotalPaid());
            $this->setBaseTotalCanceled($this->getBaseGrandTotal() - $this->getBaseTotalPaid());

            $status = $this->getStatus() === Peppermint_Sales_Model_Order::STATUS_AMENDMENT_CANCELLED
                ? Peppermint_Sales_Model_Order::STATUS_AMENDMENT_CANCELLED
                : true;

            if ($cancelState !== self::STATE_CLOSED) {
                $this->_setState($cancelState, $status, $comment);
            } else {
                // set state to closed directly because protected state can't be set manually with setState
                $this->setData('state', self::STATE_CLOSED)
                    ->addStatusHistoryComment($comment, $status)
                    ->setIsCustomerNotified(null);
            }

            // If not cancelled amendment, update admin and FE order status | order amend has it own logic
            if ($status === true) {
                Mage::helper('peppermint_orderstatus')->orderStatusMappingUpdate($this, false);
            }
        } else if (!$graceful) {
            Mage::throwException(Mage::helper('sales')->__('Order does not allow to be canceled.'));
        }

        return $this;
    }
}
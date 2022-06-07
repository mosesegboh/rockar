<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Lucaci Stefan <lucacistefan.alexandru@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Sales_Helper_OtpMail extends Mage_Core_Helper_Abstract
{
    /**
     * Generate document and send mail.
     *
     * @param object $order
     * @param object $customerData
     * @param integer $storeId
     * @return void
     */
    public function sendOtpMail($order, $customerData, $storeId)
    {
        // generate otp document
        $otpFile = $this->generateOtp($order);
        // load template fron config and init mailer
        $template = $this->getSalesConfig('email', $storeId);
        $emailTemplate = Mage::getModel('core/email_template')->load($template);
        // define template variables
        $emailVars = array_merge(
            $order->getExtraEmailVariables($order),
            [
                'otp_document' => $otpFile,
                'customer' => $customerData
            ]
        );

        // get email sender identity from config
        $configSender = $this->getSalesConfig('identity', $storeId);
        // set email sender identity
        $emailTemplate->setSenderEmail(Mage::getStoreConfig('trans_email/ident_' . $configSender . '/email', $storeId));
        $emailTemplate->setSenderName(Mage::getStoreConfig('trans_email/ident_' . $configSender . '/name', $storeId));
        // get from config otp mail settings
        $copyTo = $this->_getEmails('copy_to', $storeId);
        $copyMethod = $this->getSalesConfig('copy_method', $storeId);
        $sendTo = [
            [
                'email' => $order->getCustomerEmail(),
                'name' => $customerData->getName()
            ]
        ];

        if ($copyTo && $copyMethod == 'bcc') {
            // Add bcc to customer email
            foreach ($copyTo as $email) {
                $emailTemplate->addBcc($email);
            }
        }

        // add dealers in Bcc if config enabled.
        if (Mage::helper('peppermint_dealeremail/dealer')->isDealerCopyEnabled('peppermint_otp', $storeId)) {
            $this->_setBccDealers($emailTemplate, $order);
        }

        if ($copyTo && $copyMethod == 'copy') {
            // Add copy mail
            foreach ($copyTo as $email) {
                array_push(
                    $sendTo,
                    [
                        'email' => $email,
                        'name' => null
                    ]
                );
            }
        }
        // send email
        foreach ($sendTo as $send) {
            $emailTemplate->send($send['email'], $send['name'], $emailVars);
        }
    }

    /**
     * Generate OTP document.
     *
     * @param Peppermint_Sales_Model_Order $order
     * @return boolean|mixed
     */
    public function generateOtp($order)
    {
        $orderId = $order->getIncrementId();

        return $orderId ? Mage::helper('peppermint_checkout/pdf')->makeOtp($orderId, $order, false) : false;
    }

    /**
     * Get sale config.
     *
     * @param string $param
     * @param integer $storeId
     * @return string
     */
    public function getSalesConfig($param, $storeId = 0)
    {
        return Mage::getStoreConfig('sales_email/peppermint_otp/' . $param, $storeId);
    }

    /**
     * Process emails set on config.
     *
     * @param string $param
     * @param integer $storeId
     * @return array|boolean
     */
    protected function _getEmails($param, $storeId = 0)
    {
        // get the emails from config
        $data = $this->getSalesConfig($param, $storeId);

        return !empty($data) ? explode(',', $data) : false;
    }

    /**
     * Set Bcc dealers.
     *
     * @param [Mage_Core_Model_Abstract] $emailTemplate
     * @param [Mage_Sales_Model_Order] $order
     * @return void
     */
    protected function _setBccDealers(&$emailTemplate, $order)
    {
        $dealerEmailsList = Mage::helper('peppermint_dealeremail/dealer')->getDealerEmails($order->getDealerCode());

        if (!empty($dealerEmailsList)) {
            $dealersEmails = explode(',', $dealerEmailsList);

            foreach ($dealersEmails as $dealerEmail) {
                $emailTemplate->addBcc($dealerEmail);
            }
        }
    }
}

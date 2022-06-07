<?php

/**
 * @category     Peppermint
 * @package      Peppermint\AschroderEmail
 * @author       Catalin Lungu <catalin.lungu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_AschroderEmail_Helper_Data extends Aschroder_Email_Helper_Data
{
    /**
     * Saves in log, data related with sent email
     * 
     * @param string $to
     * @param string $template
     * @param string $subject
     * @param string $email
     * @param string $isHtml
     * @param string $attachment
     * 
     * @return $this
     */
    public function logEmailSent($to, $template, $subject, $email, $isHtml, $attachment = null)
    {
        if ($this->isLogEnabled()) {
            Mage::getModel('aschroder_email/email_log')->setEmailTo($to)
                ->setTemplate($template)
                ->setSubject($subject)
                ->setEmailBody($isHtml ? $email : nl2br($email))
                ->setAttachmentPath($attachment)
                ->save();
        }

        return $this;
    }

    /**
     * Customize path to make the file downloadable
     * 
     * @param string $attachment
     * @return null|string
     */
    public function getFileUrlPath($attachment)
    {
        if (is_null($attachment)) {
            return null;
        }
        $attachment = str_replace(Mage::getBaseDir() . DS, '', $attachment);
        $attachment = str_replace('/orders', '/orders/sent', $attachment);

        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . $attachment;
    }
}

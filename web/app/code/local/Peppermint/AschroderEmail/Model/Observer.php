<?php
/**
 * @category  Peppermint
 * @package   Peppermint_AschroderEmail
 * @author    Catalin Lungu <catalin.lungu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_AschroderEmail_Model_Observer extends Aschroder_Email_Model_Observer
{
    /**
     * Expects observer with data:
     * 'to', 'subject', 'template',
     * 'html', 'email_body', 'attachment'.
     *
     * @param $observer
     * @return Peppermint_AschroderEmail_Model_Observer
     */
    public function log($observer)
    {
        $event = $observer->getEvent();
        $attachment = $event->getAttachmentPath();
        Mage::helper('aschroder_email')->logEmailSent(
            $event->getTo(),
            $event->getTemplate(),
            $event->getSubject(),
            $event->getEmailBody(),
            $event->getHtml(),
            Mage::helper('peppermint_aschroderemail')->getFileUrlPath($attachment)
        );

        return $this;
    }

    /**
     * Moves a file from 'order' folder to 'order/sent' folder.
     *
     * @param  string                                   $observer
     * @return Peppermint_AschroderEmail_Model_Observer
     */
    public function moveAttachment($observer)
    {
        $event = $observer->getEvent();
        $attachment = $event->getAttachmentPath();

        $sentPath = dirname($attachment) . '/sent';

        if (!is_dir($sentPath)) {
            mkdir($sentPath, 0775, true);
        }
        rename($attachment, $sentPath . DS . basename($attachment));

        return $this;
    }
}

<?php

/**
 * @category     Peppermint
 * @package      Peppermint\TemplateMailer
 * @author       Catalin Lungu <catalin.lungu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_TemplateMailer_Model_Email_Template_Mailer extends Mage_Core_Model_Email_Template_Mailer
{
    /**
     * Send all emails from email list
     * @see self::$_emailInfos
     * @param string $filePath 
     * @param string $fileName 
     * @param string $fileType
     *
     * @return Peppermint_TemplateMailer_Model_Email_Template_Mailer
     */
    public function send($filePath = null, $fileName = null, $fileType = null)
    {
        $attachment = [];
        /** @var $emailTemplate Mage_Core_Model_Email_Template */
        $emailTemplate = Mage::getModel('core/email_template');
        // Add attachment only if all parameters are provided
        if ($filePath && $fileName && $fileType && $this->_addAttachement($emailTemplate, $filePath, $fileName, $fileType)) {
            $attachment = ['attachment' => $filePath . DS . $fileName];
        }

        // Send all emails from corresponding list
        while (!empty($this->_emailInfos)) {
            $emailInfo = array_pop($this->_emailInfos);
            // Handle "Bcc" recipients of the current email
            $emailTemplate->addBcc($emailInfo->getBccEmails());
            // Set required design parameters and delegate email sending to Mage_Core_Model_Email_Template
            $emailTemplate->setDesignConfig(['area' => 'frontend', 'store' => $this->getStoreId()])
                ->setQueue($this->getQueue())
                ->sendTransactional(
                    $this->getTemplateId(),
                    $this->getSender(),
                    $emailInfo->getToEmails(),
                    $emailInfo->getToNames(),
                    array_merge($this->getTemplateParams(), $attachment),
                    $this->getStoreId()
                );
        }

        return $this;
    }

    /**
     * Create attachement for email
     * 
     * @param Mage_Core_Model_Email_Template $emailTemplate
     * @param string $filePath 
     * @param string $fileName 
     * @param string $fileType
     * 
     * @return bool true if file attachment exists and has content, false otherwise
     */
    private function _addAttachement(&$emailTemplate, $filePath, $fileName, $fileType): bool
    {
        $finalPath = $filePath . DS . $fileName;
        if (file_exists($finalPath)) {
            $content = file_get_contents($finalPath);
            if ($content) {
                $emailTemplate->getMail()
                    ->createAttachment(
                        $content,
                        $fileType,
                        Zend_Mime::DISPOSITION_INLINE,
                        Zend_Mime::ENCODING_BASE64,
                        $fileName
                    );

                return true;
            }
        }

        return false;
    }
}

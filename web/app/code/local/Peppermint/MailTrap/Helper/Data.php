<?php

/**
 * @category     Peppermint
 * @package      Peppermint\MailTrap
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_MailTrap_Helper_Data extends Mage_Core_Helper_Abstract
{
    const NS_IS_ENABLED = 'peppermint_mailtrap/general/enabled';
    const NS_EMAIL_FORCE_TO = 'peppermint_mailtrap/general/email_force_to';
    const NS_EMAIL_TEMPLATES = 'peppermint_mailtrap/general/email_templates';
    const NS_DEV_MODE = 'peppermint_mailtrap/general/dev_mode';
    const EMAIL_TEMPLATE_DELIMITER = ';';

    /**
     * Returns the state of the module
     *
     * @return bool
     */
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag(self::NS_IS_ENABLED);
    }

    /**
     * Returns the state of dev mode setting
     *
     * @return bool
     */
    protected function _isDevMode()
    {
        return Mage::getStoreConfigFlag(self::NS_DEV_MODE);
    }

    /**
     * Returns the mail trap email address
     *
     * @return string
     */
    protected function _getEmailTo()
    {
        return Mage::getStoreConfig(self::NS_EMAIL_FORCE_TO);
    }

    /**
     * Returns a collection of email template names that are whitelisted
     *
     * @return string[]
     */
    protected function _getEmailTemplates()
    {
        return explode(self::EMAIL_TEMPLATE_DELIMITER, Mage::getStoreConfig(self::NS_EMAIL_TEMPLATES));
    }

    /**
     * In case of mail trap candidates, forces to send email to configured email trap
     *
     * @param string $email
     * @param string $name
     * @param string $templateCode
     * @return string[]
     */
    public function processRecipients($email, $name, $templateCode = null)
    {
        if (
            $this->_isDevMode()
            || !$templateCode
            || !in_array($templateCode, $this->_getEmailTemplates())
        ) {
            return [
                'email' => $this->_getEmailTo(),
                'name' => 'Mail trap'
            ];
        }

        return [
            'email' => $email,
            'name' => $name
        ];
    }
}

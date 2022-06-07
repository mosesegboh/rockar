<?php

/**
 * @category     Peppermint
 * @package      Peppermint\MailTrap
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_MailTrap_Model_Email extends Mage_Core_Model_Email
{
    /**
     * Override on Mage_Core_Model_Email::send
     * with the purpose of trapping the email in case MailTrap rules apply
     *
     * @return Mage_Core_Model_Email
     */
    public function send()
    {
        /** @var Peppermint_MailTrap_Helper_Data $helper */
        $helper = Mage::helper('peppermint_mailtrap');
        if ($helper->isEnabled()) {
            $selfToEmail = $this->getToEmail();

            list($email, $name) = $helper->processRecipients($selfToEmail, $this->getToName());
            if ($email != $selfToEmail) {
                $this->setToEmail($email);
                $this->setToName($name);
            }
        }

        return parent::send();
    }
}

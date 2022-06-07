<?php

/**
 * @category     Peppermint
 * @package      Peppermint\MailTrap
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_MailTrap_Model_Email_Template extends Peppermint_AschroderEmail_Model_Email_Template
{
    /**
     * Send mail to recipient
     *
     * @param   array|string       $email        E-mail(s)
     * @param   array|string|null  $name         receiver name(s)
     * @param   array              $variables    template variables
     * @return  boolean
     **/
    public function send($email, $name = null, array $variables = [])
    {
        /** @var Peppermint_MailTrap_Helper_Data $helper */
        $helper = Mage::helper('peppermint_mailtrap');
        if ($helper->isEnabled()) {
            list($email, $name) = $helper->processRecipients($email, $name, $this->getId());
        }

        return parent::send($email, $name, $variables);
    }
}

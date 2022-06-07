<?php
/**
 * @category  Peppermint
 * @package   Peppermint_NotifyMe
 * @author    Ausma Smite <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar Ltd (http://rockar.com)
 */

/**
 * Class Peppermint_NotifyMe_Helper_Data
 */
class Peppermint_NotifyMe_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Path to check if 'Notify Me' feature is enabled
     */
    public const XML_PATH_NOTIFYME_IS_ENABLED = 'peppermint_notifyme/general/enabled';

    /**
     * Path to 'Notify Me' URL
     */
    public const XML_PATH_NOTIFYME_URL = 'peppermint_notifyme/general/notifyme_url';

    /**
     * Returns the current state of 'Notify Me'
     *
     * @return bool
     */
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_NOTIFYME_IS_ENABLED);
    }

    /**
     * Get 'Notify Me' URL
     *
     * @return string
     */
    public function getNotifyMeUrl()
    {
        return $this->isEnabled() ? Mage::getStoreConfig(self::XML_PATH_NOTIFYME_URL) : '';
    }
}

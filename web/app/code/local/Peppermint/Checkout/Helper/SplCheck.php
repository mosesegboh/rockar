<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Checkout
 * @author    Ausma Smite <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Checkout_Helper_SplCheck extends Peppermint_Checkout_Helper_SplCheck_Api
{
    const SPL_CHECK_SERVICE_URL = 'spl_check/general/url';
    const SPL_CHECK_AUTH_USER = 'spl_check/general/user';
    const SPL_CHECK_AUTH_PASS = 'spl_check/general/password';

    /**
     * Get service URL from settings
     *
     * @return mixed
     */
    public function getServiceUrl()
    {
        return Mage::getStoreConfig(self::SPL_CHECK_SERVICE_URL);
    }

    /**
     * Get user from settings
     *
     * @return mixed
     */
    public function getUser()
    {
        return Mage::getStoreConfig(self::SPL_CHECK_AUTH_USER);
    }

    /**
     * Get password from settings
     *
     * @return mixed
     */
    public function getPassword()
    {
        return Mage::getStoreConfig(self::SPL_CHECK_AUTH_PASS);
    }

    /**
     * Pass GCID for SPL check
     *
     * @param string $gcid
     * @return boolean
     */
    public function getGcidCheck($gcid)
    {
        $payload = json_encode([
            'ivCustomer' => '',
            'ivGcid' => $gcid
        ]);

        return $this->_doPostRequest($this->getServiceUrl(), $payload);
    }
}
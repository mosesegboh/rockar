<?php

/**
 * @category     Peppermint
 * @package      Peppermint_Gcdm
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Gcdm_Helper_Redirector extends Mage_Core_Helper_Abstract
{
    const GCDM_CLIENT = 'dsp';
    const GCDM_COUNTRY = 'ZA';
    const GCDM_LANGUAGE = 'en';
    const GCDM_RESPONSE_TYPE = 'code';
    const GCDM_SCOPE_BASE = 'authenticate_user';
    const NS_BEFORE_GCDM_AUTH_URL = 'before_gcdm_auth_url';

    /**
     * Brand names
     */
    const BMW = 'bmw';
    const MINI = 'mini';
    const MOTORRAD = 'bmwmotorrad';

    /**
     * Gcdm endpoint to get gcid
     *
     * @var Peppermint_Gcdm_Helper_GcdmConfig
     */
    private $_gcdmConfig;

    /**
     * @var Mage_Customer_Model_Session
     */
    private $_session;

    /**
     * Constructor of helper
     */
    public function __construct()
    {
        $this->_gcdmConfig = Mage::helper('peppermint_gcdm/GcdmConfig');
        $this->_session = Mage::getSingleton('customer/session');
    }

    /**
     * Generates the redirect to one login external url
     * @return string
     */
    public function getRedirectUrl()
    {
        $gcdmState = $this->_generateState();
        Mage::getSingleton('customer/session')->setGcdmState($gcdmState);

        return rtrim($this->_gcdmConfig->getGcdmConfig('gcdm_login_url'), '/')
            . '?' . http_build_query([
                'client' => self::GCDM_CLIENT,
                'country' => self::GCDM_COUNTRY,
                'language' => self::GCDM_LANGUAGE,
                'response_type' => self::GCDM_RESPONSE_TYPE,
                'redirect_uri' => rtrim(Mage::getUrl('gcdm/index/auth'), '/'),
                'scope' => self::GCDM_SCOPE_BASE,
                'state' => $gcdmState,
                'brand' => $this->_getBrand()
            ]);
    }

    /**
     * Stores in session previous to login page
     * @return Peppermint_Gcdm_Helper_Redirector
     */
    public function rememberPreviousUrl()
    {
        $request = Mage::app()->getRequest();
        $url = $request->getControllerName() === 'onepage' && $request->getRouteName() === 'checkout'
            ? Mage::helper('core/url')->getCurrentUrl()
            : Mage::helper('core/http')->getHttpReferer();

        $this->_session->setData(self::NS_BEFORE_GCDM_AUTH_URL, $url);

        return $this;
    }

    /**
     * Gets the previous to login URL if it was saved
     * @return string|bool
     */
    public function getPreviousToLoginUrl()
    {
        return $this->_session->getData(self::NS_BEFORE_GCDM_AUTH_URL) ?: false;
    }

    /**
     * Generates a random string
     * @todo extend the method to generate a 2 way hash for scope finetunning
     * @return string
     */
    private function _generateState()
    {
        return Mage::helper('core')->getRandomString(64);
    }

    /**
     * Returns brand value parameter
     *
     * @return string
     * @throws Mage_Core_Model_Store_Exception
     */
    private function _getBrand()
    {
        $store = str_replace('_store_view', '', Mage::app()->getStore()->getCode());

        switch ($store) {
            case 'mini':
                $result = self::MINI;
                break;
            case 'motorrad':
                $result = self::MOTORRAD;
                break;
            default :
                $result = self::BMW;
                break;
        }

        return $result;
    }
}

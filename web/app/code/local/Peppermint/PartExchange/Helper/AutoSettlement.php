<?php
/**
 * @category  Peppermint
 * @package   Peppermint_PartExchange
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_PartExchange_Helper_AutoSettlement extends Peppermint_PartExchange_Helper_AutoSettlement_Api
{
    const AUTO_SETTLEMENT_SERVICE_URL = 'auto_settlement/general/url';
    const AUTO_SETTLEMENT_API_KEY = 'auto_settlement/general/api_key';
    const AUTO_SETTLEMENT_AUTH_USER = 'auto_settlement/general/user';
    const AUTO_SETTLEMENT_AUTH_PASS = 'auto_settlement/general/password';

    const GET_SETTLEMENT_QUOTES = 'getsettlementQuote';

    /**
     * Get service URL from settings
     *
     * @return mixed
     */
    public function getServiceUrl()
    {
        return Mage::getStoreConfig(self::AUTO_SETTLEMENT_SERVICE_URL);
    }

    /**
     * Get API key from settings
     *
     * @return mixed
     */
    public function getApiKey()
    {
        return Mage::getStoreConfig(self::AUTO_SETTLEMENT_API_KEY);
    }

    /**
     * Get user from settings
     *
     * @return mixed
     */
    public function getUser()
    {
        return Mage::getStoreConfig(self::AUTO_SETTLEMENT_AUTH_USER);
    }

    /**
     * Get password from settings
     *
     * @return mixed
     */
    public function getPassword()
    {
        return Mage::getStoreConfig(self::AUTO_SETTLEMENT_AUTH_PASS);
    }

    /**
     * @param string $method
     * @return string
     */
    public function getRequestUrl($method)
    {
        return $this->getServiceUrl() . '/' . $method;
    }

    /**
     * @param string $idNumber
     * @param string $phone
     * @param string $email
     * @param string $mmcode
     * @return stdClass|null
     * @throws Mage_Core_Exception
     */
    public function getSettlementQuotes($idNumber, $phone, $email, $mmcode)
    {
        $payload = json_encode([
            'userId' => $this->getUser(),
            'password' => $this->getPassword(),
            'idNumber' => $idNumber,
            'cellphone' => $phone,
            'emailAddress' => $email,
            'mmCode' => $mmcode
        ]);

        return $this->_doPostRequest($this->getRequestUrl(self::GET_SETTLEMENT_QUOTES), $payload);
    }

    /**
     * Get Settlement Terms and Conditions Page Url
     *
     * @return string
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getSettlementTermsUrl()
    {
        switch (str_replace('_store_view', '', Mage::app()->getStore()->getCode())) {
            case 'mini':
                $url = 'https://www.mini.co.za/auto_settlement_terms';
                break;
            case 'motorrad':
                $url = 'https://www.bmw-motorrad.co.za/en/auto-settlement-terms.html';
                break;
            default :
                $url = 'https://www.bmw.co.za/auto_settlement_terms';
                break;
        }

        return $url;
    }
}

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_PartExchange
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

abstract class Peppermint_PartExchange_Helper_AutoSettlement_Api extends Mage_Core_Helper_Abstract
{
    /**
     * Fetch API key from settings
     *
     * @return string
     */
    abstract public function getApiKey();

    /**
     * Builds the API request headers
     *
     * @return string[]
     */
    protected function _getRequestHeaders()
    {
        return [
            'Accept: application/json',
            'Content-Type: application/json',
            'apikey: ' . $this->getApiKey()
        ];
    }

    /**
     * Simple get request on a specified URL
     *
     * @param string $url
     * @throws Mage_Core_Exception
     * @return stdClass
     */
    protected function _doGetRequest($url)
    {
        $curl = new Varien_Http_Adapter_Curl();
        $curl->setConfig(['timeout' => 30])
            ->write(Zend_Http_Client::GET, $url, '1.1', $this->_getRequestHeaders());
        $returnData = Zend_Http_Response::extractBody($curl->read());
        $curl->close();

        return $this->_decodeReponse($returnData);
    }

    /**
     * Simple post request on a specified URL with a provided payload
     *
     * @param string $url
     * @param mixed $payload
     * @throws Mage_Core_Exception
     * @return stdClass
     */
    protected function _doPostRequest($url, $payload)
    {
        $curl = new Varien_Http_Adapter_Curl();
        $curl->setConfig(['timeout' => 30])
            ->write(Zend_Http_Client::POST, $url, '1.1', $this->_getRequestHeaders(), $payload);
        $returnData = Zend_Http_Response::extractBody($curl->read());
        $curl->close();

        return $this->_decodeReponse($returnData);
    }

    /**
     * Error handling for API
     *
     * @param string $data
     * @throws Mage_Core_Exception
     * @return stdClass
     */
    private function _decodeReponse($data)
    {
        $jsonData = json_decode($data, true);

        if (
            strlen($data) === 0
            || ($jsonData === null && json_last_error() !== JSON_ERROR_NONE)
            || $jsonData->etReturn->error
            || ($jsonData->etReturn->messages && count($jsonData->etReturn->messages))
        ) {
            Mage::log('Web service request has failed with response: ' . var_export($data, true), Zend_Log::ERR);
            Mage::throwException('Web service communication has failed, please try again later or call for support!');
        }

        return $jsonData;
    }
}

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Checkout
 * @author    Ausma Smite <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

abstract class Peppermint_Checkout_Helper_SplCheck_Api extends Mage_Core_Helper_Abstract
{
    /**
     * Builds the API request headers
     *
     * @return string[]
     */
    protected function _getRequestHeaders()
    {
        $helper = Mage::helper('peppermint_checkout/SplCheck');

        return [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($helper->getUser() . ':' . $helper->getPassword())
        ];
    }

    /**
     * Simple post request on a specified URL with a provided payload
     *
     * @param string $url
     * @param mixed $payload
     * @return boolean
     */
    protected function _doPostRequest($url, $payload)
    {
        try {
            $curl = new Varien_Http_Adapter_Curl();
            $curl->setConfig(['timeout' => 30])
                ->write(Zend_Http_Client::POST, $url, '1.1', $this->_getRequestHeaders(), $payload);
            Zend_Http_Response::extractBody($curl->read());
            $curl->close();
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return true;
    }
}

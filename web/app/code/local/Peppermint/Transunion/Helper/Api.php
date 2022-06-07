<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Transunion
 * @author    Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

abstract class Peppermint_Transunion_Helper_Api extends Mage_Core_Helper_Abstract
{
    const API_DETAILS_SUFFIX = '/valuation';
    const API_VEHICLE_TYPE_SUFFIX = '/vehicleTypes';
    const API_MAKE_SUFFIX = '/vehicleMake';
    const API_MODEL_SUFFIX = '/vehicleModel';

    /**
     * Retrieve endpoint URL for API request.
     *
     * @return string
     */
    protected function _getEndpointUrl()
    {
        return Mage::helper('peppermint_transunion')->getApiUrl();
    }

    /**
     * Retrieve endpoint API key for API request.
     *
     * @return string
     */
    protected function _getEndpointApiKey()
    {
        return Mage::helper('peppermint_transunion')->getApiKey();
    }

    /**
     * Builds a base array as TransUnion expects in vehicle details request.
     * @return string[]
     */
    protected function _getBlankRequestPayload()
    {
        return [
            'condition' => '',
            'vehicleColour' => '',
            'vehicleMMCode' => '',
            'vehicleManufactureYear' => '',
            'vehicleMileage' => '',
            'vehicleRegistrationNumber' => '',
            'vehicleVinNumber' => ''
        ];
    }

    /**
     * Performs post request on vehicle valuation endpoint.
     * @param mixed[] $payload
     * @throws Rockar_PartExchange_Exception_VehicleDetailsNotFound
     * @return stdClass
     */
    protected function _requestDetails($payload)
    {
        $response = $this->_doPostRequest($this->_getEndpointUrl() . self::API_DETAILS_SUFFIX, $payload);

        if (count($response->transunionResponse->record) === 0) {
            throw new Rockar_PartExchange_Exception_VehicleDetailsNotFound();
        }

        return $response;
    }

    /**
     * Performs nested requests for manual selection of vehicle variant.
     * For an emtpy array it performs the request on root endpoint
     * that returns vehicle types.
     * @param string[] $nestedParts
     * @throws Mage_Core_Exception
     * @return stdClass
     */
    protected function _requestNestedData($nestedParts = [])
    {
        return $this->_doGetRequest(
            count($nestedParts)
            ? $this->_getEndpointUrl() . '/' . implode('/', $nestedParts)
            : $this->_getEndpointUrl() . self::API_VEHICLE_TYPE_SUFFIX
        );
    }

    /**
     * Simple post request on a specified URL with a provided payload.
     * @param string $url
     * @param mixed[] $payload
     * @throws Mage_Core_Exception
     * @return stdClass
     */
    protected function _doPostRequest($url, $payload)
    {
        $curl = new Varien_Http_Adapter_Curl();
        $curl->setConfig(['timeout' => 30])
            ->write(
                Zend_Http_Client::POST,
                $url,
                '1.1',
                $this->_getRequestHeaders(),
                json_encode($payload)
            );
        $returnData = Zend_Http_Response::extractBody($curl->read());
        $curl->close();

        return $this->_decodeReponse($returnData);
    }

    /**
     * Simple get request on a specified URL.
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
     * Error handling for Transunion API.
     * @param string $data
     * @throws Mage_Core_Exception
     * @return stdClass
     */
    private function _decodeReponse($data)
    {
        $jsonData = json_decode($data);

        if (
            strlen($data) === 0
            || ($jsonData === null && json_last_error() !== JSON_ERROR_NONE)
            || $jsonData->etReturn->error
            || count($jsonData->etReturn->messages)
        ) {
            Mage::log('Transunion communication has failed with respose: ' . var_export($data, true), Zend_Log::ERR);
            Mage::throwException('Transunion communication has failed, please try again later or call for support!');
        }

        return $jsonData;
    }

    /**
     * Builds the API request headers.
     *
     * @return string[]
     */
    protected function _getRequestHeaders()
    {
        return [
            'Accept: application/json',
            'Content-Type: application/json',
            'apikey: ' . $this->_getEndpointApiKey()
        ];
    }
}

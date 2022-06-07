<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Dfe
 * @author    Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

abstract class Peppermint_Dfe_Helper_Soap_Abstract_Service extends \SoapClient
{
    const XMLNS = 'http://tempuri.org/';

    /**
     * @var string The API key to be used
     */
    protected $_apiKey;

    /**
     * @var string The request attempts counter
     */
    private $_maxAttempts = 10;

    /**
     * @var [] The defined classes
     */
    protected static $_classmap = [];

    /**
     * {@inheritdoc}
     * Overrides default SoapClient constructor.
     * @param string $wsdl The wsdl file to use
     * @param string $apiKey The API key to use
     * @param array $options An array of config values
     * @return Peppermint_Dfe_Helper_Soap_Abstract_Service
     */
    public function __construct($wsdl, $apiKey, array $options = [])
    {
        $this->_apiKey = $apiKey;
        $opts = [
            'http' => [
                'header' => 'apikey: ' . $this->_apiKey . "\r\nAccept-Encoding: gzip.deflate"
            ]
        ];

        $options = array_merge(
            [
                'classmap' => static::$_classmap,
                'connection_timeout' => 30,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'soap_version' => SOAP_1_2,
                'stream_context' => stream_context_create($opts),
                'trace' => true
            ],
            $options
        );

        parent::__construct($wsdl, $options);

        return $this;
    }

    /**
     * Overrides default __doRequest SoapClient method.
     *
     * @param string $request
     * @param string $location
     * @param string $action
     * @param integer $version
     * @param integer $oneWay
     * @return string
     * @todo get rid of this override and its dependencies after the errors will be fixed on the @dfe side
     */
    public function __doRequest($request, $location, $action, $version, $oneWay = 0)
    {
        $result = false;

        /*
         * Occasionally the response is not a valid xml response because Apigee returns HTTP/1.1 502 Bad Gateway
         * Please refer https://docs.apigee.com/api-platform/troubleshoot/runtime/502-bad-gateway
         * to see the possible causes
         * On other occasions the soap call returns unknown SSL protocol error in connection to 443
         * in which case we should retry the request
         */

        $attempts = 0;

        while ($attempts < $this->_maxAttempts && !$result) {
            try {
                $result = $this->_doCurlRequest($request, $location);
            } catch (Exception $e) {
                Mage::logException($e);
            }
            ++$attempts;
        }

        return $result ?: (new SimpleXMLElement('<fake>xml</fake>'))->asXML();
    }

    /**
     * Custom curl request method.
     * @param string $request
     * @param string $location
     * @throws Exception
     * @return boolean|string
     * @todo get rid of this override and its dependencies after the errors will be fixed on the @dfe side
     */
    private function _doCurlRequest($request, $location)
    {
        $headers = [
            'Content-type: application/soap+xml',
            'Accept-Encoding: gzip.deflate',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
            'Connection: Keep-Alive',
            'Content-length: ' . strlen($request),
            'apikey: ' . $this->_apiKey
        ];

        $curl = curl_init();

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_URL => $location,
            CURLOPT_POSTFIELDS => $request,
            CURLOPT_HTTPHEADER => $headers
        ];

        curl_setopt_array($curl, $options);

        $output = curl_exec($curl);

        if ($output === false) {
            Mage::throwException('Error encountered during request: ' . curl_error($curl));
        }

        if ($output === '') {
            Mage::throwException('Response is empty!');
        }

        if (curl_getinfo($curl, CURLINFO_RESPONSE_CODE) !== 200) {
            Mage::throwException('Response status code not 200!');
        }

        libxml_use_internal_errors(true);

        if (!(new DOMDocument())->loadXML($output)) {
            Mage::throwException('Response is not a valid XML: ' . var_export(libxml_get_errors(), true));
        }

        libxml_clear_errors();
        libxml_use_internal_errors(false);

        curl_close($curl);

        return $output;
    }

    /**
     * Sets the user and pass header.
     * @param string $username
     * @param string $password
     * @return Peppermint_Dfe_Helper_Soap_Abstract_Service
     */
    public function setAuthHeader($username, $password)
    {
        $this->__setSoapHeaders(new \SoapHeader(
            self::XMLNS,
            'Authentication',
            (new Peppermint_Dfe_Soap_Authentication())
                ->setAppUsername($username)
                ->setAppPassword($password),
            false
        ));

        return $this;
    }

    /**
     * Sets a custom location of the services.
     * @param string $serviceUrl
     * @return Peppermint_Dfe_Helper_Soap_Abstract_Service
     */
    public function setServiceLocation($serviceUrl)
    {
        $this->__setLocation($serviceUrl);

        return $this;
    }
}

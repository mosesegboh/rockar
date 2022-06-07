<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Gcdm
 * @author    Stefan Lucaci <lucacistefan.alexandru@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Gcdm_Helper_ExternalCommunication extends Mage_Core_Helper_Abstract
{
    const METHOD_SUFFIX_AUTH = '/oauth/token';
    const METHOD_SUFFIX_REVOKE = '/oauth/revoke';
    const METHOD_SUFFIX_USER_INFO = '/protected/dsp/ZA-BMW-en/customers/userAccount';
    const METHOD_SUFFIX_CUSTOMER_DATA = '/protected/v4/dsp/ZA-BMW-en/customers';
    const METHOD_SUFFIX_RESEND_CODE = '/public/dsp/ZA-BMW-en/workflows/mailAdministrations/customers/resend';
    const UNKNOWN_OTHER_EXCEPTIONS = 'Not one of the above';

    /**
     * Gcdm endpoint to get gcid.
     *
     * @var Peppermint_Gcdm_Helper_GcdmConfig
     */
    private $_gcdmConfig;

    /**
     * Access token for GCDM communication.
     * @var null|string
     */
    private $_accessToken;

    /**
     * Refresh token for GCDM communication.
     * @var null|string
     */
    private $_refreshToken;

    /**
     * Re auth in progress.
     * @var boolean
     */
    private $_reAuthInProgress = false;

    /**
     * Base API Url.
     * @var null|string
     */
    protected $_baseApiUrl;

    /**
     * Policy API Url.
     * @var null|string
     */
    protected $_policyApiUrl;

    /**
     * Constructor of helper.
     */
    public function __construct()
    {
        $this->_gcdmConfig = Mage::helper('peppermint_gcdm/GcdmConfig');
        $this->_baseApiUrl = $this->_gcdmConfig->getGcdmConfig('gcdm_base_api_url');
        $this->_policyApiUrl = $this->_gcdmConfig->getGcdmPolicyConfig('base_api_url');
    }

    /**
     * Setter for access token.
     * @param string $accessToken
     * @return Peppermint_Gcdm_Helper_ExternalCommunication
     */
    public function setAccessToken($accessToken)
    {
        $this->_accessToken = $accessToken;

        return $this;
    }

    /**
     * Getter for access token.
     * @throws Mage_Core_Exception
     * @return string
     */
    public function getAccessToken()
    {
        if ($this->_accessToken === null) {
            Mage::throwException($this->__('Access token is not set!'));
        }

        return $this->_accessToken;
    }

    /**
     * Setter for refresh token.
     * @param string $refreshToken
     * @return Peppermint_Gcdm_Helper_ExternalCommunication
     */
    public function setRefreshToken($refreshToken)
    {
        $this->_refreshToken = $refreshToken;

        return $this;
    }

    /**
     * Getter for refresh token.
     * @throws Mage_Core_Exception
     * @return string
     */
    public function getRefreshToken()
    {
        if ($this->_refreshToken === null) {
            Mage::throwException($this->__('Refresh token is not set!'));
        }

        return $this->_refreshToken;
    }

    /**
     * Setter for re auth in progress.
     * @param boolean $reAuthInProgress
     * @return Peppermint_Gcdm_Helper_ExternalCommunication
     */
    public function setReAuthInProgress($reAuthInProgress)
    {
        $this->_reAuthInProgress = $reAuthInProgress;

        return $this;
    }

    /**
     * Getter for re auth in progress.
     * @return boolean
     */
    public function getReAuthInProgress()
    {
        return $this->_reAuthInProgress;
    }

    /**
     * Authenticate into GCDM via return code.
     * @param string $code
     * @throws Mage_Core_Exception
     * @return stdClass
     */
    public function authUser($code)
    {
        $curl = new Varien_Http_Adapter_Curl();
        $curl->addOption(CURLOPT_CUSTOMREQUEST, Zend_Http_Client::POST)
            ->setConfig(['timeout' => 15])
            ->write(
                Zend_Http_Client::POST,
                $this->_getApiUrl(self::METHOD_SUFFIX_AUTH),
                '1.1',
                [
                    'Accept: application/json',
                    'Content-Type: application/x-www-form-urlencoded',
                    $this->_getBasicAuthHeader()
                ],
                http_build_query([
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => rtrim(Mage::getUrl('gcdm/index/auth'), '/')
                ])
            );
        $returnData = Zend_Http_Response::extractBody($curl->read());
        $curl->close();

        $authData = $this->_decodeReponse($returnData);
        $this->setAccessToken($authData->access_token)
            ->setRefreshToken($authData->refresh_token);

        return $authData;
    }

    /**
     * Authenticate into GCDM via refresh token.
     * @throws Mage_Core_Exception
     * @return stdClass|void
     */
    protected function _reAuthUser()
    {
        $curl = new Varien_Http_Adapter_Curl();
        $curl->addOption(CURLOPT_CUSTOMREQUEST, Zend_Http_Client::POST)
            ->setConfig(['timeout' => 15])
            ->write(
                Zend_Http_Client::POST,
                $this->_getApiUrl(self::METHOD_SUFFIX_AUTH),
                '1.1',
                [
                    'Accept: application/json',
                    'Content-Type: application/x-www-form-urlencoded',
                    $this->_getBasicAuthHeader()
                ],
                http_build_query([
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $this->getRefreshToken()
                ])
            );
        $returnData = Zend_Http_Response::extractBody($curl->read());
        $curl->close();

        try {
            $authData = $this->_decodeReponse($returnData);
            $this->setAccessToken($authData->access_token)
                ->setRefreshToken($authData->refresh_token)
                ->setReAuthInProgress(false);
            Mage::dispatchEvent('peppermint_re_auth', ['gcdm' => $this]);
        } catch (Mage_Core_Exception $e) {
            Mage::dispatchEvent('peppermint_re_auth', ['gcdm' => false]);
            throw $e;
        }

        return $authData;
    }

    /**
     * Call to Gcdm endpoint to get user gcid.
     * @throws Mage_Core_Exception
     * @return stdClass
     */
    protected function _checkAccessToken()
    {
        return $this->_gcdmCommunication(
            Zend_Http_Client::GET,
            ['timeout' => 15],
            $this->_getApiUrl(self::METHOD_SUFFIX_USER_INFO),
            [$this->_getUserAuthHeader()]
        );
    }

    /**
     * Call to Gcdm endpoint to get user details.
     * @throws Mage_Core_Exception
     * @return stdClass
     */
    public function getGcdmCustomerInfo()
    {
        $this->_checkAccessToken();

        return $this->_gcdmCommunication(
            Zend_Http_Client::GET,
            ['timeout' => 15],
            $this->_getApiUrl(self::METHOD_SUFFIX_CUSTOMER_DATA),
            [$this->_getUserAuthHeader()]
        );
    }

    /**
     * Call to Gcdm policy endpoint to get the current policy
     *
     * @return stdClass
     * @throws Mage_Core_Exception
     */
    public function getCurrentPolicy()
    {
        return $this->_gcdmCommunication(
            Zend_Http_Client::GET,
            ['timeout' => 15],
            $this->_getPolicyApiUrl(),
            [$this->_getApiKeyHeader('keyId')]
        );
    }

    /**
     * Call to Gcdm endpoint to resend email activation code.
     *
     * @param $loginId
     *
     * @return string
     */
    public function resendMailActivationCode($loginId)
    {
        $curl = new Varien_Http_Adapter_Curl();
        $curl->addOption(CURLOPT_CUSTOMREQUEST, Zend_Http_Client::POST)
            ->setConfig(['timeout' => 15])
            ->write(
                Zend_Http_Client::POST,
                $this->_getApiUrl(self::METHOD_SUFFIX_RESEND_CODE),
                '1.1',
                [
                    'Accept: application/json',
                    'Content-Type: application/x-www-form-urlencoded',
                    $this->_getBasicAuthHeader()
                ],
                http_build_query(
                    ['loginId' => $loginId]
                )
            );
        $returnData = Zend_Http_Response::extractBody($curl->read());
        $curl->close();

        return $returnData;
    }

    /**
     * Call to Gcdm endpoint to delete access token.
     * @throws Mage_Core_Exception
     * @return Peppermint_Gcdm_Helper_ExternalCommunication
     */
    public function revokeGcdmToken()
    {
        $this->_checkAccessToken();
        $curl = new Varien_Http_Adapter_Curl();
        $curl->addOption(CURLOPT_CUSTOMREQUEST, Zend_Http_Client::POST)
            ->setConfig(['timeout' => 15])
            ->write(
                Zend_Http_Client::POST,
                $this->_getApiUrl(self::METHOD_SUFFIX_REVOKE),
                '1.1',
                [
                    'Content-Type: application/x-www-form-urlencoded',
                    $this->_getBasicAuthHeader()
                ],
                http_build_query([
                    'token' => $this->getAccessToken()
                ])
            );
        Zend_Http_Response::extractBody($curl->read());
        $curl->close();

        return $this;
    }

    /**
     * Call to Gcdm endpoint to update user details.
     * @param [] $data
     * @throws Mage_Core_Exception
     * @return stdClass
     */
    public function putGcdmCustomerInfo($data)
    {
        $this->_checkAccessToken();
        $curl = new Varien_Http_Adapter_Curl();
        $curl->addOption(CURLOPT_CUSTOMREQUEST, Zend_Http_Client::PUT)
            ->addOption(CURLOPT_POSTFIELDS, json_encode($data))
            ->setConfig(['timeout' => 15])
            ->write(
                Zend_Http_Client::PUT,
                $this->_getApiUrl(self::METHOD_SUFFIX_CUSTOMER_DATA),
                '1.1',
                [
                    $this->_getUserAuthHeader(),
                    'Accept: application/json',
                    'Content-Type: application/json'
                ]
            );
        $response = $curl->read();
        $returnData = Zend_Http_Response::extractBody($response);
        $returnCode = (int)Zend_Http_Response::extractCode($response);
        $curl->close();

        return $this->_decodeReponse($returnData, $returnCode);
    }

    /**
     * Curl communication wrapper.
     *
     * @param string $method
     * @param array $config
     * @param string $apiUrl
     * @param array $headers
     * @throws Mage_Core_Exception
     * @return stdClass
     */
    private function _gcdmCommunication($method, $config, $apiUrl, $headers)
    {
        $curl = new Varien_Http_Adapter_Curl();
        $curl->setConfig($config)
            ->write(
                $method,
                $apiUrl,
                '1.1',
                $headers
            );
        $data = Zend_Http_Response::extractBody($curl->read());
        $curl->close();

        return $this->_decodeReponse($data);
    }

    /**
     * Handles GCDM communication issues and in case of success, returns json decoded payload.
     * @param string $data
     * @throws Mage_Core_Exception
     * @return stdClass
     */
    private function _decodeReponse($data, $status = false)
    {
        $jsonData = json_decode($data);

        if (
            strlen($data) === 0
            || ($jsonData === null && json_last_error() !== JSON_ERROR_NONE)
            || (isset($jsonData->error) && $jsonData->error)
            || (isset($jsonData->errorcode) && $jsonData->errorcode)
        ) {
            $reAuthData = false;

            if (
                isset($jsonData->error)
                && $jsonData->error === 'invalid_token'
                && !$this->getReAuthInProgress()
            ) {
                $reAuthData = $this->setReAuthInProgress(true)
                    ->_reAuthUser();
            }

            if (!$reAuthData) {
                Mage::log('GCDM communication has failed with response: ' . var_export($data, true), Zend_Log::ERR);
                Mage::throwException($data);
            }
        }

        if ($status !== false) {
            $jsonData->httpStatus = $status;
        }

        return $jsonData;
    }

    /**
     * Returns the complete URL for GCDM API call.
     * @param string $method
     * @return string
     */
    private function _getApiUrl($method)
    {
        return $this->_baseApiUrl . $method;
    }

    /**
     * Returns the complete URL for GCDM API call.
     *
     * @return string
     */
    private function _getPolicyApiUrl()
    {
        return $this->_policyApiUrl;
    }

    /**
     * Builds auth key header from client id.
     * @param string $keyName
     * @return string
     */
    private function _getApiKeyHeader($keyName)
    {
        return $keyName . ': ' . $this->_gcdmConfig->getGcdmPolicyConfig('api_key');
    }

    /**
     * Builds Basic auth header from client id and secret.
     * @return string
     */
    private function _getBasicAuthHeader()
    {
        return 'Authorization: Basic ' . base64_encode($this->_gcdmConfig->getGcdmConfig('gcdm_client_id') . ':' . $this->_gcdmConfig->getGcdmConfig('gcdm_secret'));
    }

    /**
     * Build Bearer header for the authenticated user.
     * @return string
     */
    private function _getUserAuthHeader()
    {
        return 'Authorization: Bearer ' . $this->getAccessToken();
    }

    /**
     * Process the error from GDCM if there is an status code
     * otherwise use the default status code "Not one of the above" from GCDM mapped error if it exists.
     * If the default error message dosen't exist use the original error message
     * that was passed through in the $fallbackResponse
     *
     * @param string $fallbackResponse
     * @param string|bool $errorResponse
     * @return string
     */
    public function processError($fallbackResponse, $errorResponse = false)
    {
        $errorsMapping = Mage::helper('peppermint_gcdm/gcdmConfig')->getGcdmErrorMappingConfigArray();
        $defaultError = $this->getGcdmDefaultErrorMessage();
        $response = $defaultError ?: $fallbackResponse;
        $jsonData = json_decode($errorResponse);
        $isJsonValid = (isset($jsonData, $jsonData->errorcode) && json_last_error() === JSON_ERROR_NONE);

        if (is_array($errorsMapping)
            && $isJsonValid
        ) {
            if (!empty($defaultError)) {
                $response = $defaultError . " {Error code: $jsonData->errorcode}";
            }

            foreach ($errorsMapping as ['error_code' => $code, 'error_message' => $message]) {
                if ($code === $jsonData->errorcode) {
                    $response = $message;
                    break;
                }
            }
        }

        return $response;
    }

    /**
     * Get the default GCDM error message from the error mapping if it exists
     *
     * @return string|null
     */
    public function getGcdmDefaultErrorMessage()
    {
        $result = null;
        $errorsMapping = Mage::helper('peppermint_gcdm/gcdmConfig')->getGcdmErrorMappingConfigArray();

        if (is_array($errorsMapping)) {
            foreach ($errorsMapping as ['error_code' => $code, 'error_message' => $message]) {
                if ($code === self::UNKNOWN_OTHER_EXCEPTIONS) {
                    $result = $message;
                    break;
                }
            }
        }

        return $result;
    }
}

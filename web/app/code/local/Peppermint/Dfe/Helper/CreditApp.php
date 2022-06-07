<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Dfe
 * @author    Taras Kapushchak <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Dfe_Helper_CreditApp extends Peppermint_Dfe_Helper_Api
{
    const CREDIT_APP_SERVICE_URL = 'peppermint_dfe/credit_app/url';
    const CREDIT_APP_API_KEY = 'peppermint_dfe/credit_app/api_key';
    const CREDIT_APP_AUTH_USER = 'peppermint_dfe/credit_app/user';
    const CREDIT_APP_AUTH_PASS = 'peppermint_dfe/credit_app/pass';
    const CREDIT_APP_EXPIRED_PERIOD = 'peppermint_dfe/credit_app/code_expired_time';
    const CREDIT_APP_ATTEMPTS = 'peppermint_dfe/credit_app/attempts_number';

    const GET_CUSTOMER_DATA = 'GetCustomerData';

    /**
     * Get service URL from settings
     *
     * @return mixed
     */
    public function getServiceUrl()
    {
        return Mage::getStoreConfig(self::CREDIT_APP_SERVICE_URL);
    }

    /**
     * Get API key from settings
     *
     * @return mixed
     */
    public function getApiKey()
    {
        return Mage::getStoreConfig(self::CREDIT_APP_API_KEY);
    }

    /**
     * Get user from settings
     *
     * @return mixed
     */
    public function getUser()
    {
        return Mage::getStoreConfig(self::CREDIT_APP_AUTH_USER);
    }

    /**
     * Get password from settings
     *
     * @return mixed
     */
    public function getPassword()
    {
        return Mage::getStoreConfig(self::CREDIT_APP_AUTH_PASS);
    }

    /**
     * Get expired period from settings
     *
     * @return mixed
     */
    public function getExpiredPeriod()
    {
        return Mage::getStoreConfig(self::CREDIT_APP_EXPIRED_PERIOD);
    }

    /**
     * Get allowed attempts number from settings
     *
     * @return mixed
     */
    public function getAttemptsNumber()
    {
        return Mage::getStoreConfig(self::CREDIT_APP_ATTEMPTS);
    }

    /**
     * Construct request URL for the passed method
     *
     * @param $method
     * @return string
     */
    public function getRequestUrl($method)
    {
        return $this->getServiceUrl() . '/' . $method;
    }

    /**
     * @param $clientId
     * @return stdClass|null
     * @throws Mage_Core_Exception
     */
    public function getCustomerData($clientId)
    {
        $payload = json_encode([
            'clientId' => $clientId,
            'idType' => 'string',
            'userId' => $this->getUser(),
            'password' => $this->getPassword()
        ]);

        return $this->_doPostRequest($this->getRequestUrl(self::GET_CUSTOMER_DATA), $payload);
    }

    /**
     * Prepare response without any user data
     *
     * @param [] $response
     * @return mixed
     * @throws Mage_Core_Exception
     */
    public function prepareShortResponse($response)
    {
        $result['loginResponse'] = $response['loginResponse'];
        $result['authentication'] = [
            'isClientPresent' => $response['authentication']['isClientPresent'],
            'cellNoForOTP' => $this->parseOtpPhoneNumber($response['authentication']['cellNoForOTP']),
            'otpLength' => mb_strlen($response['authentication']['otp']),
            'retryAttempts' => $this->getRetryAttempts(true)
        ];

        return $result;
    }

    /**
     * Prepare full response, remove password from otp
     *
     * @param [] $response
     * @return mixed
     * @throws Mage_Core_Exception
     */
    public function prepareFullResponse($response)
    {
        unset($response['authentication']['otp']);
        $response['authentication']['cellNoForOTP'] =
            $this->parseOtpPhoneNumber($response['authentication']['cellNoForOTP']);

        return $response;
    }

    /**
     * Prepare phone number to display in popup
     *
     * @param $string
     * @return string|null
     */
    public function parseOtpPhoneNumber($string)
    {
        if (!$string) {
            return null;
        }

        if (strpos($string, '|')) {
            $result = explode('|', $string);
            $result[0] = $this->formatOtpPhoneNumber($result[0]);
            $result = implode('|', $result);
        } else {
            $result = $this->formatOtpPhoneNumber($string);
        }

        return $result;
    }

    /**
     * Replace part of phone number with stars
     *
     * @param $string
     * @return string|null
     */
    public function formatOtpPhoneNumber($string)
    {
        return $string ? '******' . substr($string, strlen($string) - 4, 4) : null;
    }

    /**
     * Calculate available retry attempts
     *
     * @param bool $reset
     * @return mixed
     */
    public function getRetryAttempts($reset = false)
    {
        $attemptsNumber = $this->getAttemptsNumber();

        // Limit attempts only if configuration contains some value more then '0'
        if (is_numeric($attemptsNumber) && $attemptsNumber > 0) {
            $session = Mage::getSingleton('checkout/session');
            $currentAttempt = $session->getRetryAttempts();

            if ($currentAttempt === null || $reset) {
                $session->setRetryAttempts($attemptsNumber);

                return (int)$attemptsNumber;
            }

            return (int)$currentAttempt;
        }

        return false;
    }
}

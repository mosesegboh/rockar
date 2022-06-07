<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Admin
 * @author    Lilian Codreanu <lilian.codreanu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Admin_Model_Sgate extends Mage_Core_Model_Abstract
{
    const OPENID_SCOPE = 'openid%20profile%20email';
    const OPENID_RESPONSE_TYPE = 'code';
    const OPENID_NONCE = 'tbdbyRockar';
    const SESSION_KEY_OPENID_NONCE = 'open_id_nonce';
    const SESSION_KEY_EMAIL = 'open_id_person';
    const SESSION_KEY_ROLE = 'open_id_role';
    const ACCESS_TOKEN = 'access_token';
    const REFRESH_TOKEN = 'refresh_token';
    const OIDC_TOKEN = 'oidc_token';

    /**
     * @var Peppermint_Admin_Model_Config
     */
    private $_config;

    /**
     * @var string
     */
    private $_nonce;

    /**
     * @param string $role
     * @return $this
     */
    protected function _prepareAuthorization(string $role)
    {
        $user = Mage::getSingleton('admin/session');
        $user->addData([
            self::SESSION_KEY_OPENID_NONCE => $this->getNonce(),
            self::SESSION_KEY_ROLE => $role
        ]);

        return $this;
    }

    /**
     * Return generated Nonce.
     *
     * @return string
     */
    public function getNonce()
    {
        if (!$this->_nonce) {
            $this->_nonce = md5(self::OPENID_NONCE . time());
        }

        return $this->_nonce;
    }

    /**
     * Return S-Gate Authorized Email.
     * @return mixed
     */
    public function getAuthorizedEmail()
    {
        return Mage::getSingleton('admin/session')->getData(self::SESSION_KEY_EMAIL);
    }

    /**
     * Get URL associated to a role
     *
     * @param  string $role
     * @return string
     */
    protected function _getRoleUrl($role)
    {
        return Mage::getModel('peppermint_admin/role')->load($role, 'role')
            ->getUrl() ?? $this->_getConfig()->getBaseUrl();
    }

    /**
     * Generate S-Gate Authorize URI.
     *
     * @param string $role
     * @return string
     */
    public function getAuthorizeUri(string $role)
    {
        $this->_prepareAuthorization($role);

        return $this->_getRoleUrl($role) .
            'oauth2/realms/root/realms/' . $this->_getConfig()->getRealmPath($role) .
            '/authorize?client_id=' . $this->_getConfig()->getClientId($role) .
            '&scope=' . self::OPENID_SCOPE .
            '&redirect_uri=' . $this->getRedirectUrl($role) .
            '&response_type=' . self::OPENID_RESPONSE_TYPE .
            '&nonce=' . $this->getNonce();
    }

    /**
     * Return S-Gate Redirect URL.
     *
     * @param $role
     * @throws Mage_Core_Model_Store_Exception
     * @return string
     */
    public function getRedirectUrl($role)
    {
        return Mage::app()->getStore(1)->getBaseUrl() . 'ecp?role=' . $role;
    }

    /**
     * Return Module Config Model.
     *
     * @return false|Peppermint_Admin_Model_Config
     */
    private function _getConfig()
    {
        if (!$this->_config) {
            $this->_config = Mage::getModel('peppermint_admin/config');
        }

        return $this->_config;
    }

    /**
     * Return Access Token URI.
     *
     * @param string $code
     * @param string $role
     * @return string
     */
    public function getAccessTokenUri(string $code, string $role)
    {
        return trim($this->_getConfig()->getBaseUrl()) .
            'oauth2/realms/root/realms/' . $this->_getConfig()->getRealmPath($role) .
            '/access_token?grant_type=authorization_code&code=' . $code .
            '&redirect_uri=' . $this->getRedirectUrl($role);
    }

    /**
     * Generate Revoke Token URI.
     *
     * @param string $role
     * @param string $token
     * @return string
     */
    public function getRevokeTokenUri(string $role, string $token)
    {
        return trim($this->_getConfig()->getBaseUrl()) .
            'oauth2/realms/root/realms/' . $this->_getConfig()->getRealmPath($role) .
            '/token/revoke?client_id=' . $this->_getConfig()->getClientId($role) .
            '&client_secret=' . $this->_getConfig()->getClientSecret($role) .
            '&token=' . $token;
    }

    /**
     * Generate End Session URI (to log out of OAuth app).
     *
     * @param string $role
     * @param string $token
     * @return string
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getEndSessionUri(string $role, string $token)
    {
        return trim($this->_getConfig()->getBaseUrl()) .
            'oauth2/realms/root/realms/' . $this->_getConfig()->getRealmPath($role) .
            '/connect/endSession?id_token_hint=' . $token .
            '&post_logout_redirect_uri=' . $this->getRedirectUrl($role);
    }

    /**
     * Request AccessToken to S-Gate server.
     *
     * @param string $uri
     * @param string $role
     *
     * @throws \Exception
     * @return array
     */
    public function retryAccessToken(string $uri, string $role)
    {
        $config = [
            'adapter' => 'Zend_Http_Client_Adapter_Curl',
            'ssltransport' => 'tls'
        ];

        $client = new Zend_Http_Client($uri, $config);
        $client->setHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
            'authorization' => 'Basic ' . $this->_getAuthorizationKey($role)
        ]);

        $request = $client->request('POST');

        if ($request->getStatus() !== 200) {
            throw new \Exception($request->getMessage());
        }

        return (array) Mage::helper('rockar_all')->jsonDecode($request->getBody());
    }

    /**
     * Revoke AccessToken to S-Gate server.
     *
     * @param string $uri
     * @return boolean
     * @throws Zend_Http_Client_Exception|\Exception
     */
    public function revokeAccess(string $uri)
    {
        $config = [
            'adapter' => 'Zend_Http_Client_Adapter_Curl',
            'ssltransport' => 'tls'
        ];

        $client = new Zend_Http_Client($uri, $config);
        $client->setHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded'
        ]);

        $request = $client->request('POST');

        if ($request->getStatus() !== 200) {
            throw new \Exception($request->getMessage());
        }

        return true;
    }

    /**
     * Check if is valid nonce what was get send S-Gate server.
     *
     * @param string $nonce
     * @return boolean
     */
    public function isValidNonce(string $nonce)
    {
        return $nonce === Mage::getSingleton('admin/session')->getData(self::SESSION_KEY_OPENID_NONCE);
    }

    /**
     * Return Curl Header Authorization Key.
     *
     * @param $role
     * @return string
     */
    private function _getAuthorizationKey($role)
    {
        return base64_encode(
            $this->_getConfig()->getClientId($role) . ':' . $this->_getConfig()->getClientSecret($role)
        );
    }

    /**
     * Using the Bearer Token, return the email address assoicated with this session
     *
     * @param  string $token
     * @param  string $role
     * @return string
     * @throws Zend_Http_Client_Exception
     */
    public function validateUsersEmail($token, $role)
    {
        $url = trim($this->_getConfig()->getBaseUrl()) .
            'oauth2/realms/root/realms/' . $this->_getConfig()->getRealmPath($role) .
            '/userinfo';

        $config = [
            'adapter' => 'Zend_Http_Client_Adapter_Curl',
            'ssltransport' => 'tls'
        ];

        $client = new Zend_Http_Client($url, $config);
        $client->setHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
            'authorization' => 'Bearer ' . $token
        ]);

        $request = $client->request('GET');

        if ($request->getStatus() !== 200) {
            throw new Exception($request->getMessage());
        }

        $request = (array) Mage::helper('rockar_all')->jsonDecode($request->getBody());

        return $request['email'] ?? '';
    }
}

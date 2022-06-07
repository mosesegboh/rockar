<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Admin
 * @author    Lilian Codreanu <lilian.codreanu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Admin_Model_Config
{
    /**
     * General S-Gate configuration: base url.
     */
    const XML_PATH_SGATE_GENERAL_BASE_URL = 'peppermint_sgate/general/base_url';
    /**
     * General S-Gate configuration: redirect uri.
     */
    const XML_PATH_SGATE_GENERAL_REDIRECT_URI = 'peppermint_sgate/general/redirect_uri';

    /**
     * @var array
     */
    protected $_roles;

    /**
     * Peppermint_Admin_Model_Config constructor.
     */
    public function __construct()
    {
        $this->_roles = $this->_role ?? Mage::getModel('peppermint_admin/role')->getRoleCollection();
    }

    /**
     * Get S-Gate server base URL.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return Mage::getStoreConfig(self::XML_PATH_SGATE_GENERAL_BASE_URL);
    }

    /**
     * Get S-Gate Client ID.
     *
     * @param $role
     * @return null|string
     */
    public function getClientId($role)
    {
        return $this->_roles[$role]['client_id'] ?? null;
    }

    /**
     * Get S-Gate Client Secret.
     *
     * @param $role
     * @return null|string
     */
    public function getClientSecret($role)
    {
        return $this->_roles[$role]['client_secret'] ?? null;
    }

    /**
     * Get S-Gate Realm Path.
     *
     * @param $role
     * @return null|string
     */
    public function getRealmPath($role)
    {
        return $this->_roles[$role]['realm'] ?? null;
    }
}

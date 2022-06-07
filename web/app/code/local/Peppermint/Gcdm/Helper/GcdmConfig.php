<?php
/**
 * @category     Peppermint
 * @package      Peppermint_Gcdm
 * @author       Stefan Lucaci <lucacistefan.alexandru@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Gcdm_Helper_GcdmConfig extends Mage_Core_Helper_Abstract
{
    const XML_GCDM_PATH_ERROR_MAPPING = 'peppermint_gcdm/error_mapping/errors';

    /**
     * Get GCDM general configuration
     *
     * @param $param
     * @return array
     */
    public function getGcdmConfig($param)
    {
        return Mage::getStoreConfig('peppermint_gcdm/general/' . $param);
    }

    /**
     * Get Policy API configuration
     *
     * @param $param
     * @return array
     */
    public function getGcdmPolicyConfig($param)
    {
        return Mage::getStoreConfig('peppermint_gcdm/contact_policy_consents/' . $param);
    }

    /**
     * Get GCDM error code and message from config mapping
     *
     * @return array
     * @throws Exception
     */
    public function getGcdmErrorMappingConfigArray()
    {
        return Mage::helper('core/unserializeArray')->unserialize(
            Mage::getStoreConfig(self::XML_GCDM_PATH_ERROR_MAPPING)
        );
    }
}

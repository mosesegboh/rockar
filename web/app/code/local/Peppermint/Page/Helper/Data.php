<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Page
 * @author    Chris Plant <christopher.plant@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Page_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Gets referrer data URL from previous URL visited
     *
     * @return string
     */
    public function getReferrerUrl()
    {
        return (string) Mage::app()->getRequest()->getServer('HTTP_REFERER', '');
    }

    /**
     * Gets Request URL parameters
     *
     * @return string
     */
    public function getRequestParams()
    {
        $urlParams = $this->_getRequest()->getParams();

        if (isset($urlParams['id'])) {
            unset($urlParams['id']);
        }

        return !empty($urlParams) ? '\'' . http_build_query($urlParams) . '\'' : '';
    }
}

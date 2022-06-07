<?php
/**
 * @category  Peppermint
 * @package   Peppermint_All
 * @author    Ausma Smite <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar Ltd (http://rockar.com)
 */

class Peppermint_All_Helper_NewJourney extends Mage_Core_Helper_Abstract
{
    /**
     * XML paths
     */
    const XML_PATH_DESIGN_WEBSITE_WEBSITE_IDS = 'design/website/website_ids';
    const XML_PATH_DESIGN_WEBSITE_LOCATION_ICON_URL = 'design/website/location_icon_url';
    const XML_PATH_DESIGN_WEBSITE_LOGO_LANDING_PAGE = 'design/website/website_logo';
    const XML_PATH_DESIGN_WEBSITE_LOGO_OTHER_PAGES = 'design/website/website_logo_other';

    /**
     * Get website IDs
     *
     * @return string
     */
    public function getWebsiteIds(): string
    {
        return Mage::getStoreConfig(self::XML_PATH_DESIGN_WEBSITE_WEBSITE_IDS) ?? '';
    }

    /**
     * Check if brand is selected
     *
     * @return bool
     * @throws Mage_Core_Model_Store_Exception
     */
    public function isBrandSelected(): bool
    {
        $websiteId = Mage::app()->getStore()->getWebsiteId();
        $selectedBrands = explode(',', $this->getWebsiteIds());

        return in_array($websiteId, $selectedBrands);
    }

    /**
     * Get location icon URL
     *
     * @return string
     */
    public function getLocationIconUrl(): string
    {
        return Mage::getStoreConfig(self::XML_PATH_DESIGN_WEBSITE_LOCATION_ICON_URL);
    }

    /**
     * Get Landing Page Logo Url
     *
     * @return string
     * @throws Exception
     */
    public function getLandingPageLogoUrl(): string
    {
        $configValue = Mage::getStoreConfig(self::XML_PATH_DESIGN_WEBSITE_LOGO_LANDING_PAGE);

        if ($configValue) {
            return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) .
                'website/navigation/logo' . DS . $configValue;
        }

        return Mage::getDesign()->getSkinUrl('images/bmw-logo-ondark.png');
    }

    /**
     * Get Other Pages Logo Url
     *
     * @return string
     * @throws Exception
     */
    public function getOtherPagesLogoUrl(): string
    {
        $configValue = Mage::getStoreConfig(self::XML_PATH_DESIGN_WEBSITE_LOGO_OTHER_PAGES);

        if ($configValue) {
            return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) .
                'website/navigation/logo_other' . DS . $configValue;
        }

        return Mage::getDesign()->getSkinUrl('images/bmw-logo-onlight.png');
    }
}

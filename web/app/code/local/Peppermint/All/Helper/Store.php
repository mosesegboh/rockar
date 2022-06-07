<?php
/**
 * @category  Peppermint
 * @package   Peppermint_All
 * @author    Jiraphong witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_All_Helper_Store extends Mage_Core_Helper_Abstract
{
    /**
     * @var string
     */
    const DEMO_STORE_CODE = 'demo';

    /**
     * Getter Demo store code
     *
     * @return string
     */
    public function getDemoStoreCode(): string
    {
        return self::DEMO_STORE_CODE;
    }

    /**
     * Return id of Demo website
     *
     * @return int|null
     */
    public function getDemoWebSiteId()
    {
        foreach (Mage::app()->getStores() as $storeId => $store) {
            if ($store->getCode() === self::DEMO_STORE_CODE) {
                return $store->getWebsiteId();
            }
        }

        return null;
    }

    /**
     * Return id of Demo store
     *
     * @return int|null
     */
    public function getDemoStoreId()
    {
        foreach (Mage::app()->getStores() as $storeId => $store) {
            if ($store->getCode() === self::DEMO_STORE_CODE) {
                return $store->getId();
            }
        }

        return null;
    }
}

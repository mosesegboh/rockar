<?php
/**
 * @category  Peppermint
 * @package   Peppermint_CatalogRule
 * @author    Jiraphong witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_CatalogRule_Model_System_Config_Source_WebsiteIds
{
    /**
     * Website label/value array getter without demo store, compatible with form dropdown options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $demoStoreCode = Mage::helper('peppermint_all/store')->getDemoStoreCode();

        foreach (Mage::app()->getWebsites() as $website) {
            if ($website->getCode() !== $demoStoreCode) {
                $options[] = [
                    'label' => $website->getName(),
                    'value' => $website->getId(),
                ];
            }
        }

        return $options;
    }
}
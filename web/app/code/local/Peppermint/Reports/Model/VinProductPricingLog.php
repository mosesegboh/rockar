<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Reports
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Reports_Model_VinProductPricingLog extends Mage_Core_Model_Abstract
{
    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('peppermint_reports/vinProductPricingLog');
    }

    /**
     * Get select values array for attribute
     * since this is for log reports, it is keyed by value instead of option id
     *
     * @param $attributeCode
     * @return array
     */
    public function getSelectValues($attributeCode)
    {
        $return = [];
        $options = $this->_getOptions($attributeCode);

        foreach ($options as $option) {
            $return[$option['label']] = $option['label'];
        }

        return $return;
    }

    /**
     * Retrieve attribute options array
     *
     * @param $attribute
     * @return array
     */
    protected function _getOptions($attribute)
    {
        $attribute = Mage::getSingleton('eav/config')
            ->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attribute);

        $options = [];
        if ($attribute->usesSource()) {
            $options = $attribute->getSource()
                ->getAllOptions(false);
        }

        return $options;
    }
}

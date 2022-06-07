<?php
/**
 * @category  Peppermint
 * @package   Peppermint_ShortfallAllowance
 * @author    Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

class Peppermint_ShortfallAllowance_Model_Adminhtml_System_Config_Source_Attribute_Options
{
    /**
     * Options getter.
     *
     * @param $attribute string 
     * @return array
     */
    public function toOptionArray($attribute)
    {
        return $this->_getOptions($attribute);
    }

    /**
     * get options array for attribute
     * 
     * @param $attribute string
     * @return array
     */
    public function toArray($attribute)
    {
        $return = [];

        $options = $this->_getOptions($attribute);

        foreach ($options as $option) {
            $return[$option['value']] = $option['label'];
        }

        return $return;
    }

    /**
     * _getOptions.
     *
     * @param $attribute string
     * @return array
     */
    protected function _getOptions($attribute)
    {
        $attribute = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attribute);

        $options = [];

        if ($attribute->usesSource()) {
            $options = $attribute->getSource()
                ->getAllOptions(false);
        }

        return $options;
    }
}

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_ProductPods
 * @author    Taras Kapushchak <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_ProductPods_Model_Adminhtml_System_Config_Source_Attributes
{
    /**
     * toOptionArray
     *
     * @param bool $includeAllOptions
     * @param bool $showEmptyOption
     * @return array
     */
    public function toOptionArray($includeAllOptions = false, $showEmptyOption = false)
    {
        $options = [];

        if ($showEmptyOption) {
            array_unshift($options, ['label' => Mage::helper('adminhtml')->__('-- Please Select --'), 'value' => '']);
        }

        if ($includeAllOptions) {
            array_unshift($options, ['label' => Mage::helper('adminhtml')->__('All'), 'value' => '0']);
        }

        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addFieldToSelect('attribute_code')
            ->addFieldToFilter('is_user_defined', ['eq' => '1'])
            ->getItems();

        foreach($attributes as $attribute) {
            $options[] = ['label' => $attribute->getAttributeCode(), 'value' => $attribute->getAttributeCode()];
        }

        return $options;
    }

    /**
     * toArray
     *
     * @param bool $includeAllOptions
     * @return array
     */
    public function toArray($includeAllOptions = false)
    {
        $options = [];

        if ($includeAllOptions) {
            array_unshift($options, Mage::helper('adminhtml')->__('All'));
        }

        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addFieldToSelect('attribute_code')
            ->addFieldToFilter('is_user_defined', ['eq' => '1'])
            ->getItems();

        foreach($attributes as $attribute) {
            $options[$attribute->getAttributeCode()] = $attribute->getAttributeCode();
        }

        return $options;
    }
}

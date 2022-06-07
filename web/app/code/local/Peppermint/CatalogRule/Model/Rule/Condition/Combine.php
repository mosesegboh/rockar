<?php
/**
 * @category  Peppermint
 * @package   Peppermint_CatalogRule
 * @author    Juris Krislauks <techteam@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_CatalogRule_Model_Rule_Condition_Combine extends Mage_CatalogRule_Model_Rule_Condition_Combine
{
    /**
     * Adds default catalogRule combine condition choices and adds extra section for date difference rules.
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        /** @var Peppermint_CatalogRule_Helper_Data $catalogRuleHelper */
        $catalogRuleHelper = Mage::helper('peppermint_catalogrule');

        $productAttributes = Mage::getModel('catalogrule/rule_condition_product')->loadAttributeOptions()
            ->getAttributeOption();

        $comparableDateAttributes = [];

        foreach ($catalogRuleHelper->getComparableDateAttributes() as $attributeCode) {
            if ($realAttribute = $productAttributes[$attributeCode] ?? false) {
                $comparableDateAttributes[] = [
                    'value' => 'peppermint_catalogrule/rule_condition_dateDifference|' . $attributeCode,
                    'label' => $realAttribute
                ];
            }
        }

        $conditions = parent::getNewChildSelectOptions();

        return array_merge_recursive(
            $conditions,
            [
                [
                    'label' => $catalogRuleHelper->__('Product Day Difference Attributes'),
                    'value' => $comparableDateAttributes
                ]
            ]
        );
    }
}

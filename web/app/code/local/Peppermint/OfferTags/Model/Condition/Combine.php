<?php
/**
 * @category  Peppermint
 * @package   Peppermint_OfferTags
 * @author    Ketevani Revazishvili<techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_OfferTags_Model_Condition_Combine extends Mage_Rule_Model_Condition_Combine
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('offertags/condition_combine');
    }

    /**
     * Generate a conditions data
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $productCondition = Mage::getModel('catalogrule/rule_condition_product');
        $productAttributes = $productCondition->loadAttributeOptions()->getAttributeOption();
        $attributes = [];

        foreach ($productAttributes as $code=>$label) {
            $attributes[] = ['value'=>'catalogrule/rule_condition_product|'.$code, 'label'=>$label];
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, [
            ['value'=>'peppermint_offertags/condition_combine', 'label'=>Mage::helper('catalogrule')->__('Conditions Combination')],
            ['label'=>Mage::helper('catalogrule')->__('Product Attribute'), 'value'=>$attributes],
        ]);

        return $conditions;
    }

    /**
     * Collect validated attributes
     *
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $productCollection
     * @return Peppermint_OfferTags_Model_Condition_Combine
     */
    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }

        return $this;
    }


}

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_CatalogRule
 * @author    Juris Krislauks <techteam@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_CatalogRule_Model_Rule_Condition_DateDifference extends Mage_Rule_Model_Condition_Product_Abstract
{
    /**
     * Get attribute name - used on condition rule select.
     *
     * @return string
     */
    public function getAttributeName()
    {
        return parent::getAttributeName() . ' day difference';
    }

    /**
     * Get attribute element type for displaying in admin view.
     *
     * @return string
     */
    public function getValueElementType()
    {
        return 'text';
    }

    /**
     * Get attribute type to process validation - only numeric values.
     *
     * @return string
     */
    public function getInputType()
    {
        return 'numeric';
    }

    /**
     * Prepares SQL condition for this rule.
     * Will filter out all products where date attribute is compared based on php now date.
     *
     * @return mixed|string
     */
    public function prepareConditionSql()
    {
        $alias = 'cpf';
        $attribute = $this->getAttribute();
        $values = (array) $this->getValueParsed();
        $operator = $this->correctOperator($this->getOperator(), $this->getInputType());
        $currDate = now(true);

        $realValues = [];

        foreach ($values as $value) {
            $realValues[] = "date_add(`{$alias}`.`{$attribute}`, interval {$value} day)";
        }

        /** @var Mage_Rule_Model_Resource_Rule_Condition_SqlBuilder $ruleResource */
        $ruleResource = $this->getRuleResourceHelper();
        $result = $ruleResource->getOperatorCondition($currDate, $operator, $realValues);

        // remove the ` marks since date is not a attribute but still needs to be compared as a value
        $result = str_replace("`{$currDate}`", "'{$currDate}'", $result);

        // remove single-quotes around date_add method(s)
        foreach ($realValues as $real) {
            $result = str_replace("'{$real}'", (string) $real, $result);
        }

        return $result;
    }
}

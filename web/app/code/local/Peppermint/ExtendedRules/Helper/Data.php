<?php

/**
 * @category     Peppermint
 * @package      Peppermint_ExtendedRules
 * @author       Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_ExtendedRules_Helper_Data extends Rockar_ExtendedRules_Helper_Data
{
    const EXTENDED_COLOUR_RULE = 'colour';
    const EXTENDED_MILEAGE_RULE = 'mileage';
    /**
     * Apply all deductions and get rid of negative total
     *
     * @param Varien_Object $object
     * @return $this
     */
    public function getProcessedDeductedValues(Varien_Object $object)
    {
        if ($this->isExtendedRulesEnabled()) {
            Mage::helper('rockar_extendedrules/deductions_conditionSlider')->deduct($object);
            $mapping = unserialize(Mage::getStoreConfig($this::RULES_MAPPING_PATH));
            $sortedArray = [];
            foreach ($mapping as $helper => $helperData) {
                $sortedArray[$helper] = $helperData['sort_order'];
            }
            asort($sortedArray);
            foreach (array_keys($sortedArray) as $deductionHelper) {
                $this->_setHelperByRule(
                    $deductionHelper,
                    $object,
                    $this->deductionValues[$mapping[$deductionHelper]['deduct_from']]
                );
            }
            $this->_removeNegativeValues($object);
        }

        return $this;
    }

    /**
     * Use specific deduction helper
     * Skip deduction only for extended colour rule if defined
     *
     * @param string $rule
     * @param Varien_Object $object
     * @param string $deductFrom
     * @return void
     */
    private function _setHelperByRule($rule, $object, $deductFrom)
    {
        switch ($rule) {
            case self::EXTENDED_MILEAGE_RULE:
                if (class_exists('Peppermint_ExtendedRules_Helper_Deductions_' . ucfirst($rule))) {
                    Mage::helper('peppermint_extendedrules/deductions_' . $rule)->deduct(
                        $object,
                        $deductFrom
                    );
                }
                break;
            case self::EXTENDED_COLOUR_RULE:
                break;
            default:
                if (class_exists('Rockar_ExtendedRules_Helper_Deductions_' . ucfirst($rule))) {
                    Mage::helper('rockar_extendedrules/deductions_' . $rule)->deduct(
                        $object,
                        $deductFrom
                    );
                }
                break;
        }
    }
}

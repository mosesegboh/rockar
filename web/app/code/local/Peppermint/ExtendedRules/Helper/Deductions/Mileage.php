<?php

/**
 * @category     Peppermint
 * @package      Peppermint_ExtendedRules
 * @author       Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_ExtendedRules_Helper_Deductions_Mileage extends Rockar_ExtendedRules_Helper_DeductionsAbstract
{
    /**
     * Adjusts vehicle valuation value based on mileage rules defined in extended rules
     *
     * @param Varien_Object $object
     * @param string $deductFrom
     * @return $this
     */
    public function deduct(Varien_Object $object, $deductFrom)
    {
        $mileage = (int) $object->getData('mileage');
        $rule = Mage::getModel('peppermint_extendedrules/mileage')->getCollection()
            ->addFieldToFilter('mileage_from', ['lteq' => $mileage])
            ->addFieldToFilter('mileage_to', ['gteq' => $mileage])
            ->setCurPage(1)
            ->setPageSize(1)
            ->getFirstItem();

        if ($rule->getId()) {
            $totals = $object->getData('totals');
            $updatedTotal = $totals['total'];
            $deductionValue = (float) $rule->getDeductionValue();
            $deductionType = (int) $rule->getDeductionType();

            if ($deductionType == Rockar_PartExchange_Model_Adminhtml_System_Config_Source_DeductionType::DEDUCTION_TYPE_PERCENTAGE) {
                $updatedTotal += $totals[$deductFrom] * ($deductionValue / 100);
            } else {
                $updatedTotal += $deductionValue;
            }

            $totals['total'] = $updatedTotal;
            $updatedTotals['totals'] = $totals;
            $object->addData($updatedTotals);
        }

        return $this;
    }
}

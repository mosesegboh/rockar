<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Jiraphong Witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_FinancingOptions_Helper_Otp extends Mage_Core_Helper_Abstract
{
    /**
     * check if order is pay in full finance
     *
     * @param array $financeData
     *
     * @return bool
     */
    public function isPayInFull($financeData)
    {
        return $financeData['is_pay_in_full'] ?? false;
    }

    /**
     * Get Pay In Full FinanceAmount
     *
     * @param array $financeData
     * @param float $pxValue
     *
     * @return int|float
     */
    public function getPayInFullFinanceAmount($financeData, $pxValue)
    {
        $financeAmount = $pxValue <= 0 ? ($financeData['total_amount_payable'] ?? 0) : ($financeData['balance_to_finance'] ?? 0);

        return $financeAmount < 0 ? 0 : $financeAmount;
    }

    /**
     * Get the total cost of accessories from order item finance data variables
     *
     * @param array $financeDataVars
     * @return int
     */
    public function getAccessoriesTotal($financeDataVars)
    {
        $accessoriesTotal = 0;

        foreach (($financeDataVars['car_data'] ?? []) as $data) {
            if (isset($data['group']) && $data['group'] === Mage::helper('rockar_all')->__('Accessories')) {
                $accessoriesTotal = $data['price'] ?? 0;
            }
        }

        return $accessoriesTotal;
    }
}

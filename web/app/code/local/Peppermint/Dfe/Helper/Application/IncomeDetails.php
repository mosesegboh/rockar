<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Dfe
 * @author    Robert Ionas <robert.ionas@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Dfe_Helper_Application_IncomeDetails extends Mage_Core_Helper_Abstract
{
    /**
     * Set data for income details.
     *
     * @param integer $orderId
     * @return object
     */
    public function setIncomeDetailsData($orderId)
    {
        $quoteData = Mage::getModel('rockar_checkout/order_additional')->load($orderId, 'order_id')
            ->getData();

        return (new Peppermint_Dfe_Soap_Application_IncomeDetails(
            $quoteData['monthly_gross_salary'] ?? 0,
            $quoteData['car_allowance'] ?? 0,
            $quoteData['take_home_salary'] ?? 0,
            $quoteData['additional_income'] ?? 0,
            0,
            $quoteData['bond_rent_payment'] ?? 0,
            $quoteData['vehicle_installments'] ?? 0,
            $quoteData['credit_card_repayments'] ?? 0,
            $quoteData['clothing_accounts'] ?? 0,
            $quoteData['policy_repayments'] ?? 0,
            $quoteData['transport_cost'] ?? 0,
            $quoteData['education_cost'] ?? 0,
            $quoteData['household_expenses'] ?? 0,
            $quoteData['water_electricity_expenses'] ?? 0,
            $quoteData['personal_loan_repayment'] ?? 0,
            $quoteData['furniture_accounts'] ?? 0,
            $quoteData['over_draft_repayments'] ?? 0,
            $quoteData['telephone_payments'] ?? 0,
            $quoteData['food_and_entertainment'] ?? 0,
            $quoteData['maintenance_expenses'] ?? 0,
            $quoteData['other_expenses'] ?? 0,
            $quoteData['rent_amount'] ?? 0,
            $quoteData['medical_expenses'] ?? 0,
            $quoteData['average_of_three_months_salary'] ?? 0
        ))->setSouceOfAdditionalIncome($quoteData['souce_of_additional_income'] ?? '');
    }
}

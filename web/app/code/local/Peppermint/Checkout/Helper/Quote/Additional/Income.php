<?php
/**
 * @category  Peppermint
 * @package   Peppermint\Checkout
 * @author    Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Checkout_Helper_Quote_Additional_Income extends Mage_Core_Helper_Abstract
{
    /**
     * Returns an empty data set
     *
     * @return mixed[]
     */
    protected function _getEmptyIncomeData()
    {
        return [
            'monthly_gross_salary' => 0,
            'average_of_three_months_salary' => 0,
            'car_allowance' => 0,
            'take_home_salary' => 0,
            'additional_income' => 0,
            'total_monthly_income' => 0,
            'bond_rent_payment' => 0,
            'vehicle_installments' => 0,
            'credit_card_repayments' => 0,
            'clothing_accounts' => 0,
            'policy_repayments' => 0,
            'transport_cost' => 0,
            'education_cost' => 0,
            'household_expenses' => 0,
            'water_electricity_expenses' => 0,
            'personal_loan_repayment' => 0,
            'furniture_accounts' => 0,
            'over_draft_repayments' => 0,
            'telephone_payments' => 0,
            'food_and_entertainment' => 0,
            'maintenance_expenses' => 0,
            'other_expenses' => 0,
            'rent_amount' => 0,
            'medical_expenses' => 0,
            'souce_of_additional_income' => '',
            'liable_as_surety' => 0,
            'liable_as_gaurantor' => 0,
            'liable_as_co_debtor' => 0,
            'liable_as_comments' => ''
        ];
    }

    /**
     * Loads quote additional model
     *
     * @return Peppermint_Checkout_Model_Quote_Additional
     * @throws Mage_Core_Exception
     */
    protected function _loadQuoteAdditional()
    {
        $quote = Mage::getSingleton('checkout/type_onepage')->getQuote();
        if (!$quote) {
            Mage::throwException($this->__('Quote data not found, please redo the checkout process!'));
        }

        return Mage::getModel('rockar_checkout/quote_additional')->load($quote->getId(), 'quote_id')
            ->addData(['quote_id' => $quote->getId()]);
    }

    /**
     * Returns the url for income save
     *
     * @return string
     */
    public function getIncomeDetailsAction()
    {
        return $this->_getUrl('checkout/onepage/incomeDetails', [
            'form_key' => Mage::getSingleton('core/session')->getFormKey()
        ]);
    }

    /**
     * Returns the data from rockar additional table that is specific to income
     *
     * @return []
     */
    public function getData()
    {
        $emptyData = $this->_getEmptyIncomeData();
        try {
            $storedData = $this->_loadQuoteAdditional()
                ->getData();
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);

            return $emptyData;
        }

        return array_merge(
            $emptyData,
            array_intersect_key(
                $storedData,
                $emptyData
            )
        );
    }

    /**
     * Saves the income related data to rockar additional table
     *
     * @param [] $payload
     * @return Peppermint_Checkout_Helper_Quote_Additional_Income
     */
    public function saveData($payload)
    {
        $this->_loadQuoteAdditional()->addData(
            array_merge(
                [
                    'total_monthly_income' => $payload['monthly_gross_salary']
                    + $payload['average_of_three_months_salary']
                    + $payload['additional_income']
                ],
                $payload
            )
        )->save();

        return $this;
    }
}

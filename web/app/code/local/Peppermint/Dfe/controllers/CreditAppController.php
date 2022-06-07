<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Dfe
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Dfe_CreditAppController extends Mage_Core_Controller_Front_Action
{
    /**
     * Get User Info from Credit app api
     *
     * @return Zend_Controller_Response_Abstract
     */
    public function getUserInfoAction()
    {
        try {
            $customerSession = Mage::getSingleton('customer/session');
            $helper = Mage::helper('peppermint_dfe/creditApp');
            $response = $helper->getCustomerData($customerSession->getCustomer()->getSouthAfricanIdNumber());
            $customerSession->setCreditAppInfo($response);
            $response = $helper->prepareShortResponse($response);
            $customerSession->setTimerExpires(
                strtotime('+' . Mage::helper('peppermint_dfe/creditApp')->getExpiredPeriod() . ' seconds')
            );
        } catch (Exception $e) {
            $response = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }

        return $this->_sendJsonResponse($response);
    }

    /**
     * Check entered OTP code and return success or fail answer
     *
     * @return Mage_Core_Controller_Response_Http|Zend_Controller_Response_Abstract
     */
    public function submitCodeAction()
    {
        if (Mage::getSingleton('customer/session')->getTimerExpires() < strtotime('now')) {
            return $this->_sendJsonResponse(
                [
                    'success' => false,
                    'error' => $this->__('Your session has timed out. Please request a new OTP to continue or skip this step')
                ]
            );
        }

        $code = $this->getRequest()->getParam('otp_code');

        $creditAppInfo = Mage::getSingleton('customer/session')->getCreditAppInfo();
        $retryAttempts = Mage::helper('peppermint_dfe/creditApp')->getRetryAttempts();

        if ($retryAttempts === 0) {
            $response = [
                'success' => false,
                'error' => $this->__('Sorry, no more attempts are allowed.'),
                'retryAttempts' => $retryAttempts
            ];
        } elseif ($code === $creditAppInfo['authentication']['otp']) {
            $preparedCreditAppInfo = Mage::helper('peppermint_dfe/creditApp')->prepareFullResponse($creditAppInfo);
            $this->_saveAdditionalQuoteData($preparedCreditAppInfo);
            $this->_savePopupState();
            $response = [
                'success' => true,
                'creditAppData' => $preparedCreditAppInfo
            ];
        } else {
            if ($retryAttempts && $retryAttempts > 0) {
                $retryAttempts--;
                Mage::getSingleton('checkout/session')->setRetryAttempts($retryAttempts);
            }

            $response = [
                'success' => false,
                'error' => $this->__('Sorry, entered code does not match. Please ty again.'),
                'retryAttempts' => $retryAttempts
            ];
        }

        return $this->_sendJsonResponse($response);
    }

    /**
     * Persist popup state in quote
     *
     * @param int $state
     * @return $this
     */
    protected function _savePopupState($state = 1)
    {
        Mage::getSingleton('checkout/session')->getQuote()
            ->setOtpPopupPassed($state)
            ->save();

        return $this;
    }

    /**
     * Save values from credit app response to quote additional data tables
     *
     * @param [] $creditAppInfo
     */
    protected function _saveAdditionalQuoteData($creditAppInfo)
    {
        $additionalData = [
            'home_tel' => $creditAppInfo['personalDetails']['homeNumber'],
            'contact_number' => $creditAppInfo['personalDetails']['homeNumberTelephone'],
            'gender' => $creditAppInfo['personalDetails']['gender'],
            'race' => $creditAppInfo['personalDetails']['ethenicGroup'],
            'preferred_language' => $creditAppInfo['personalDetails']['preferredLanguage'],
            'marital_status' => $creditAppInfo['personalDetails']['maritalStatus'],
            'marriage_type' => $creditAppInfo['personalDetails']['maritalContract'],
            'spouse_first_name' => $creditAppInfo['personalDetails']['spouseName'],
            'spouse_last_name' => $creditAppInfo['personalDetails']['spouseSurname'],
            'spouse_id_type' => $creditAppInfo['personalDetails']['spouseIDType'],
            'spouse_id_no' => $creditAppInfo['personalDetails']['spouseIDNumber'],
            'spouse_cell_number' => $creditAppInfo['personalDetails']['spouseMobileNo'],
            'spouse_email' => $creditAppInfo['personalDetails']['spouseEmailAddress'],
            'kin_name' => $creditAppInfo['personalDetails']['nokFullName'],
            'kin_tel' => $creditAppInfo['personalDetails']['nokMobileNumber'],
            'monthly_gross_salary' => $creditAppInfo['incomeAndExpenses']['grossMonthySalary'],
            'average_of_three_months_salary' => $creditAppInfo['incomeAndExpenses']['commission'],
            'car_allowance' => $creditAppInfo['incomeAndExpenses']['carAllowance'],
            'additional_income' => $creditAppInfo['incomeAndExpenses']['additionalIncome'],
            'souce_of_additional_income' => $creditAppInfo['incomeAndExpenses']['sourceOfIncome'],
            'take_home_salary' => $creditAppInfo['incomeAndExpenses']['netSalary'],
            'total_monthly_income' => $creditAppInfo['incomeAndExpenses']['totalIncome'],
            'bond_rent_payment' => $creditAppInfo['incomeAndExpenses']['bond'],
            'rent_amount' => $creditAppInfo['incomeAndExpenses']['rent'],
            'maintenance_expenses' => $creditAppInfo['incomeAndExpenses']['maintenanceExpenses'],
            'household_expenses' => $creditAppInfo['incomeAndExpenses']['houseHoldExpenses'],
            'vehicle_installments' => $creditAppInfo['incomeAndExpenses']['vehicleInstalments'],
            'transport_cost' => $creditAppInfo['incomeAndExpenses']['transportCost'],
            'credit_card_repayments' => $creditAppInfo['incomeAndExpenses']['creditCardPayment'],
            'clothing_accounts' => $creditAppInfo['incomeAndExpenses']['clothingAccount'],
            'policy_repayments' => $creditAppInfo['incomeAndExpenses']['policyPayments'],
            'personal_loan_repayment' => $creditAppInfo['incomeAndExpenses']['personalLoans'],
            'over_draft_repayments' => $creditAppInfo['incomeAndExpenses']['overdraftPayment'],
            'furniture_accounts' => $creditAppInfo['incomeAndExpenses']['furnitureAccount'],
            'education_cost' => $creditAppInfo['incomeAndExpenses']['educationCost'],
            'water_electricity_expenses' => $creditAppInfo['incomeAndExpenses']['rateWaterAndElectricity'],
            'telephone_payments' => $creditAppInfo['incomeAndExpenses']['telephonePayment'],
            'food_and_entertainment' => $creditAppInfo['incomeAndExpenses']['foodEntertainment'],
            'medical_expenses' => $creditAppInfo['incomeAndExpenses']['medicalAid'],
            'other_expenses' => $creditAppInfo['incomeAndExpenses']['otherExpenses']
        ];

        $residenceData = [
            'ownership' => $creditAppInfo['residentialInformation']['residentialStatus'],
            'accommodation_type' => $creditAppInfo['residentialInformation']['propertyOwner'],
            'house_status' => $creditAppInfo['residentialInformation']['bondStatus'],
            'address_1' => $creditAppInfo['residentialInformation']['present_Postal_AddressLine1'],
            'address_2' => $creditAppInfo['residentialInformation']['present_Postal_AddressLine2'],
            'region' => $creditAppInfo['residentialInformation']['present_Postal_Suburb'],
            'city' => $creditAppInfo['residentialInformation']['present_Postal_City'],
            'postcode' => $creditAppInfo['residentialInformation']['present_Postal_PostalCode']
        ];

        $employmentData = [
            'employment_status' => $creditAppInfo['employmentInformation']['employementStatus'],
            'occupation' => $creditAppInfo['employmentInformation']['occupation'],
            'employment_industry' => $creditAppInfo['employmentInformation']['industry'],
            'current_employer' => $creditAppInfo['employmentInformation']['employer'],
            'employers_phone' => $creditAppInfo['employmentInformation']['currentEmployerPhoneNo'],
            'previous_employer' => $creditAppInfo['employmentInformation']['previousEmployer']
        ];

        $model = Mage::getModel('peppermint_checkout/quote_additional');
        $model->saveCustomerAdditionalData($additionalData);
        $model->saveCustomerResidenceData($residenceData);
        $model->saveCustomerEmploymentData($employmentData);
    }

    /**
     * Prepare and send data in JSON format
     *
     * @param $response
     * @return Mage_Core_Controller_Response_Http|Zend_Controller_Response_Abstract
     */
    protected function _sendJsonResponse($response)
    {
        return $this->getResponse()
            ->setHeader('Content-type', 'application/json')
            ->setBody(Mage::helper('rockar_all')->jsonEncode($response));
    }
}

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Dfe
 * @author    Lucian Mesaros <lucian.mesaros@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Dfe_Helper_Application_FinanceDetails extends Mage_Core_Helper_Abstract
{
    protected static $_financeDataToSelect = [
        'cash_price',
        'optional_final_payment',
        'interest_rate',
        'annual_contract_mileage',
        'duration_of_agreement_pcp',
        'duration_of_agreement',
        'balloon_amount',
        'customer_deposit'
    ];

    /**
     * Set data for finance details.
     *
     * @param Peppermint_Sales_Model_Order $order
     * @return object
     */
    public function setFinanceDetailsData($order)
    {
        $data = $this->_prepareData($order);

        return (new Peppermint_Dfe_Soap_Application_FinanceDetails(0, 0, 0))->setQuoteDate($data['quote_date'])
            ->setSubProductID($data['sub_product_id'])
            ->setDeposit($data['customer_deposit'])
            ->setClientInterestRate($data['interest_rate'])
            ->setDealerContributionAmount(0)
            ->setResidualValue($data['residual_value'])
            ->setTenureMonths($data['tenure_months'])
            ->setKilometer($data['annual_contract_mileage'])
            ->setRetailPrice($data['cash_price'])
            ->setDiscountAmount($data['discount_amount'])
            ->setCashDepositSourceCode($data['deposit_source'])
            ->setCashDepositOtherDetails($data['deposit_other_description']);
    }

    /**
     * Prepare collection data by order.
     *
     * @param Peppermint_Sales_Model_Order $order
     * @return []
     */
    protected function _prepareData($order)
    {
        $orderData = Mage::getModel('sales/order')->load($order->getId())
            ->getData();
        $orderItemData = Mage::helper('rockar_checkout/order')->getFirstVisibleItem($order)
            ->getData();

        $financeOverlay = Mage::helper('core')->jsonDecode($orderItemData['finance_overlay']);
        $financeData = [];
        $residualValue = 0;
        $tenureMonths = 0;

        if (!isset($financeOverlay['options'])) {
            $financeData['cash_price'] = 0;
            $financeData['optional_final_payment'] = 0;
            $financeData['interest_rate'] = 0;
            $financeData['annual_contract_mileage'] = 0;
            $financeData['duration_of_agreement_pcp'] = 0;
        } else {
            foreach ($financeOverlay['options'] as $option) {
                foreach ($option['variables'] as ['variable' => $variableName, 'value' => $value]) {
                    if (isset(array_flip(self::$_financeDataToSelect)[$variableName])) {
                        $financeData[$variableName] = $value;
                    }
                }

                if (isset($option['is_leasing_payment'])
                    && $option['is_leasing_payment'] === 1
                ) {
                    $isLeasing = true;
                }
            }
            // set by default to Instalment sale finance
            $residualValue = $financeData['balloon_amount'] ?? 0;
            $tenureMonths = $financeData['duration_of_agreement'] ?? 0;
        }

        if ($isLeasing) {
            $tenureMonths = 0;

            if (isset($financeData['duration_of_agreement_pcp'])) {
                $tenureMonths = $financeData['duration_of_agreement_pcp'];
            }

            $residualValue = 0;

            if (isset($financeData['optional_final_payment'])) {
                $residualValue = $financeData['optional_final_payment'];
            }
        }

        return [
            'quote_date' => DateTime::createFromFormat('Y-m-d H:i:s', $orderData['created_at'])->format('Ymd') ?? 0,
            'sub_product_id' => $orderData['finance_payment_type'] ?? '',
            'interest_rate' => $financeData['interest_rate'] ?? 0,
            'residual_value' => $residualValue,
            'tenure_months' => $tenureMonths,
            'annual_contract_mileage' => $financeData['annual_contract_mileage'] ?? 0,
            'cash_price' => $financeData['cash_price'] ?? 0,
            'discount_amount' => $orderItemData['discount_amount'] ?? 0,
            'customer_deposit' => $financeData['customer_deposit'] ?? 0,
            'deposit_source' => $orderItemData['deposit_source'] ?? '',
            'deposit_other_description' => $orderItemData['deposit_other_description'] ?? ''
        ];
    }
}

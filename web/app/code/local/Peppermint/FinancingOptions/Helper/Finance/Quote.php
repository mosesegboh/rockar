<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_FinancingOptions_Helper_Finance_Quote extends Mage_Core_Helper_Abstract
{
    /**
     * Get Finance Quote Data.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array                      $savedFinanceData
     * @param array                      $accessories
     * @param string                     $calcType
     *
     * @return array
     */
    public function getFinanceQuoteData($product, $savedFinanceData, $accessories, $calcType)
    {
        $core = Mage::helper('rockar_all');
        $helper = Mage::helper('financing_options');

        $savedDataPerFinanceGroup = $savedFinanceData;

        if (isset($savedFinanceData['group_id'], $savedFinanceData[$savedFinanceData['group_id']])) {
            $savedDataPerFinanceGroup = $savedFinanceData[$savedFinanceData['group_id']];
        }

        $functionParams = Mage::helper('peppermint_financingoptions/interfaces_quoteData')::prepareParams(
            $product,
            $savedDataPerFinanceGroup['mileage'] ?? 0,
            $savedDataPerFinanceGroup['term'] ?? 0,
            $savedDataPerFinanceGroup['deposit'] ?? 0,
            $savedDataPerFinanceGroup['depositMultiple'] ?? 0,
            $savedDataPerFinanceGroup['maintenance'] ?? 0,
            $calcType,
            $accessories,
            $savedFinanceData['payment_type'],
            $savedFinanceData['group_id'],
            false,
            $savedDataPerFinanceGroup['balloonPercentage'] ?? 0
        );

        $financeData = $helper->getFinanceQuoteData($functionParams);

        return [
            'product_id' => $product->getId(),
            'car_data' => $core->jsonEncode($financeData['car_data']),
            'model_code' => $financeData['model_code'],
            'lead_time' => $financeData['lead_time'],
            'finance_variables' => $core->jsonEncode($financeData['finance_variables']),
            'customer_deposit' => $financeData['customer_deposit'],
            'pay_deposit' => $financeData['pay_deposit'],
            'finance_params' => $core->jsonEncode($savedFinanceData),
            'finance_slider_steps' => Mage::helper('financing_options/config')->getAllSliderSteps(),
            'pay_in_full_payment' => $core->jsonEncode($helper->getDefaultPayInFullPayment()),
            'active_payment' => $core->jsonEncode($helper->getActivePayment($savedDataPerFinanceGroup)),
            'rockar_price' => $financeData['rockar_price'],
            'save_off_rrp' => $financeData['save_off_rrp'],
            'monthly_price' => $financeData['monthly_price'],
            'cash_deposit' => $financeData['cash_deposit'],
            'cashback' => $financeData['cashback'],
            'finance_url' => Mage::getUrl('financing/ajax/options', ['_secure' => true]),
            'payment_save_url' => Mage::getUrl('financing/ajax/saveOnCheckout', ['_secure' => true]),
            'hire_payments' => $helper->getHirePayments(),
            'is_hire' => $financeData['is_hire'],
            'is_leasing' => $financeData['is_leasing'],
            'is_pay_in_full' => $financeData['is_pay_in_full'],
            'is_instalment' => $financeData['is_instalment'] ?? false,
            'is_business' => $financeData['is_business']
        ];
    }
}

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

final class Peppermint_FinancingOptions_Helper_Interfaces_QuoteData
{
    /**
     * Prepare params for Rockar_FinancingOptions_Helper_Data::getFinanceQuoteData().
     *
     * @param integer|Mage_Catalog_Model_Product $productId
     * @param integer                            $mileage
     * @param integer                            $term
     * @param float                              $deposit
     * @param integer                            $depositMultiple
     * @param integer                            $maintenance
     * @param string                             $calcType
     * @param array                              $accessories
     * @param string                             $paymentType
     * @param integer                            $groupId
     * @param boolean|string                     $couponCode
     * @param float                              $balloonPercentage
     *
     * @return Rockar_FinancingOptions_Model_Interfaces_QuoteData
     */
    public static function prepareParams(
        $productId,
        $mileage,
        $term,
        $deposit,
        $depositMultiple,
        $maintenance,
        $calcType,
        $accessories,
        $paymentType,
        $groupId,
        $couponCode = false,
        $balloonPercentage = 0
    ) {
        $quoteData = Mage::getModel('rockar_financingoptions/interfaces_quoteData');

        $data = [
            'product_id' => $productId,
            'mileage' => $mileage,
            'term' => $term,
            'deposit' => $deposit,
            'deposit_multiple' => $depositMultiple,
            'maintenance' => $maintenance,
            'calc_type' => $calcType,
            'accessories' => $accessories,
            'payment_type' => $paymentType,
            'group_id' => $groupId,
            'balloon_percentage' => $balloonPercentage
        ];

        if ($couponCode) {
            $data['coupon_code'] = $couponCode;
        }

        $quoteData->setData($data);

        return $quoteData;
    }
}

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

final class Peppermint_FinancingOptions_Helper_Interfaces_OptionsByParams
{
    /**
     * Prepare params for Rockar_FinancingOptions_Helper_Data::getFinanceQuoteData().
     *
     * @param integer|Mage_Catalog_Model_Product $productId
     * @param integer                            $mileage
     * @param integer                            $term
     * @param float                          $deposit
     * @param integer                            $depositMultiple
     * @param integer                            $maintenance
     * @param string                         $calcType
     * @param boolean|string                    $couponCode
     * @param float                          $balloonPercentage
     *
     * @return Rockar_FinancingOptions_Model_Interfaces_OptionsByParams
     */
    public static function prepareParams(
        $productId,
        $mileage,
        $term,
        $deposit,
        $depositMultiple,
        $maintenance,
        $calcType,
        $couponCode = false,
        $balloonPercentage = 0
    ) {
        $optionsByParams = Mage::getModel('rockar_financingoptions/interfaces_optionsByParams');

        $data = [
            'product_id' => $productId,
            'mileage' => (int) $mileage,
            'term' => (int) $term,
            'deposit' => $deposit,
            'deposit_multiple' => (int) $depositMultiple,
            'maintenance' => (int) $maintenance,
            'calc_type' => $calcType,
            'balloon_percentage' => (int) $balloonPercentage
        ];

        if ($couponCode) {
            $data['coupon_code'] = $couponCode;
        }

        $optionsByParams->setData($data);

        return $optionsByParams;
    }
}

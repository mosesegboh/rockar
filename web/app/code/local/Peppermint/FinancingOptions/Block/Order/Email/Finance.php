<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Jiraphong Witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_FinancingOptions_Block_Order_Email_Finance extends Rockar_FinancingOptions_Block_Order_Email_Finance
{
    /**
     * Rewrite of core function to use formatPriceBaseOnCurrencySetting.
     * {@inheritdoc}
     */
    public function getFormattedValue($data)
    {
        if (isset($data['value'])) {
            return $data['value'];
        }

        if (isset($data['price'])) {
            return $this->formatPriceBaseOnCurrencySetting($data['price']);
        }

        if (isset($data['number'])) {
            return number_format($data['number'], 0);
        }

        return '';
    }

    /**
     * Format price based on currency setting.
     *
     * @param float $price
     * @param int $precision
     *
     * @return string
     */
    public function formatPriceBaseOnCurrencySetting($price, $precision = 2)
    {
        return Mage::getModel('directory/currency')->load(Mage::app()->getStore()->getBaseCurrencyCode())
            ->formatPrecision($price, $precision, [], false);
    }
}

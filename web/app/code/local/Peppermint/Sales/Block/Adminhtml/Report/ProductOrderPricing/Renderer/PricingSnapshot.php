<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Sales_Block_Adminhtml_Report_ProductOrderPricing_Renderer_PricingSnapshot extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Datetime
{
    /**
     * {@inheritdoc}
     */
    public function render(Varien_Object $row)
    {
        $snapshot = (array) Mage::helper('rockar_all')->jsonDecode($row->getData('pricing_details_snapshot'));
        $value = '';

        $currency = Mage::getModel('directory/currency')->load(Mage::app()->getStore()->getBaseCurrencyCode());

        if ($snapshot) {
            $originalOrderDate = $row->getData('original_created_at') ?: $row->getData('created_at');

            $originalOrderDate = Mage::app()->getLocale()
                ->date($originalOrderDate, Varien_Date::DATETIME_INTERNAL_FORMAT)
                ->toString($this->_getFormat());

            foreach ($snapshot as $key => $val) {
                if ($key !== 'options') {
                    $value .= $key . ': ' . $currency->formatPrecision($val, 2, [], false) . '<br>';
                }
            }

            $value .= 'created at: ' . $originalOrderDate;
        }

        return $value;
    }
}

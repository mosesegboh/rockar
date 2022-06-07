<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Sales_Block_Adminhtml_Report_ProductOrderPricing_Renderer_PriceRules
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * {@inheritdoc}
     */
    public function render(Varien_Object $row)
    {
        return $this->_formatRulesArray($row);
    }

    /**
     * {@inheritdoc}
     */
    public function renderExport(Varien_Object $row)
    {
        return $this->_formatRulesArray($row, false);
    }

    /**
     * Sort price rules array
     *
     * @param $a array
     * @param $b array
     * @return bool
     */
    protected function _sortPricingSnapshot($a, $b)
    {
        return ((int) $a['rule_priority'] > (int) $b['rule_priority']);
    }

    /**
     * Format Discount Amouont
     *
     * @param $rule
     * @return string
     * @throws Mage_Core_Model_Store_Exception
     */
    protected function _formatDiscountAmount($rule, $isHtml = true)
    {
        $value = (float) $rule['rule_discount_amount'];

        switch ($rule['rule_apply']) {
            case Mage_SalesRule_Model_Rule::BY_PERCENT_ACTION:
            case Mage_SalesRule_Model_Rule::TO_PERCENT_ACTION:
                $value .= '%';
                break;
            case Mage_SalesRule_Model_Rule::BY_FIXED_ACTION:
            case Mage_SalesRule_Model_Rule::TO_FIXED_ACTION:
                $value = Mage::getModel('directory/currency')
                    ->load(Mage::app()->getStore()->getBaseCurrencyCode())
                    ->formatPrecision($value, 2, [], false);
        }

        return $value;
    }

    /**
     * Format Rules Array
     *
     * @param Varien_Object $row
     * @param bool          $isHtml
     * @return string
     * @throws Mage_Core_Model_Store_Exception
     */
    protected function _formatRulesArray(Varien_Object $row, $isHtml = true)
    {
        $value = $this->_getValue($row);
        $result = '';
        $snapshot = (array) Mage::helper('rockar_all')->jsonDecode($value);

        if ($snapshot) {
            usort($snapshot, [$this, '_sortPricingSnapshot']);

            foreach ($snapshot as $rule) {
                $result .= 'ID: ' . $rule['rule_id'] . ' / ' .
                    'Name: ' . $rule['rule_name'] . ' / ' .
                    'Apply: ' . $rule['rule_apply'] . ' / ' .
                    'Discount Amount: ' . $this->_formatDiscountAmount($rule) . ' / ' .
                    'Priority: ' . $rule['rule_priority'] . ($isHtml ? '<br>' : "  |  ");
            }
        } else {
            $result = $value;
        }

        return $result;
    }
}

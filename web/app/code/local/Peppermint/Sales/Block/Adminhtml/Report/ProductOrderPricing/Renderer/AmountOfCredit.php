<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Sales_Block_Adminhtml_Report_ProductOrderPricing_Renderer_AmountOfCredit
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * {@inheritdoc}
     */
    public function render(Varien_Object $row)
    {
        $financeDataVariables = (array) Mage::helper('rockar_all')->jsonDecode($row->getData('finance_data_variables'))['finance_variables'];
        $index = array_search('amount_of_credit', array_column($financeDataVariables, 'variable'));
        $value = '';

        if ($index) {
            $value = Mage::getModel('directory/currency')->load(Mage::app()->getStore()->getBaseCurrencyCode())
                ->formatPrecision($financeDataVariables[$index]['value'], 2, [], false);
        }

        return $value;
    }
}

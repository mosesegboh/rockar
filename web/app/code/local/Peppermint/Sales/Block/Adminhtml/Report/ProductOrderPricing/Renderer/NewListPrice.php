<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Mariam Khelashvili <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Sales_Block_Adminhtml_Report_ProductOrderPricing_Renderer_NewListPrice
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * {@inheritdoc}
     */
    public function render(Varien_Object $row)
    {
        $financeDataVariables = (array) Mage::helper('rockar_all')->jsonDecode($row->getData('pricing_details_snapshot'));
        $productPrice = $row->getData('price');
        $newListPrice = (float) $financeDataVariables['price'] ?? $productPrice;
        $value = '';

        if ($newListPrice) {
            $value = Mage::getModel('directory/currency')->load(Mage::app()->getStore()->getBaseCurrencyCode())
                ->formatPrecision($newListPrice, 2, [], false);
        }

        return  $value;
    }
}
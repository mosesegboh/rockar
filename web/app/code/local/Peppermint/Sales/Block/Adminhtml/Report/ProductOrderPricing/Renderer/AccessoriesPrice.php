<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Mariam Khelashvili <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Sales_Block_Adminhtml_Report_ProductOrderPricing_Renderer_AccessoriesPrice
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * {@inheritdoc}
     */
    public function render(Varien_Object $row)
    {
        $financeDataVariables = (array) Mage::helper('rockar_all')->jsonDecode($row->getData('finance_data_variables'));
        $value = '';

        if (isset($financeDataVariables['car_data'])) {
            foreach ($financeDataVariables['car_data'] as $data) {
                if ($data['group'] === 'Accessories') {
                    $value = Mage::getModel('directory/currency')->load(Mage::app()->getStore()->getBaseCurrencyCode())
                        ->formatPrecision($data['price'], 2, [], false);
                    break;
                }
            }
        }

        return $value;
    }
}
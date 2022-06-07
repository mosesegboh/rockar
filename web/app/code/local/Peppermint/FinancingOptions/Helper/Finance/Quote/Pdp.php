<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_FinancingOptions_Helper_Finance_Quote_Pdp extends Rockar2_FinancingOptions_Helper_Finance_Quote_Pdp
{
    /**
     * Return Finance Quote Data.
     *
     * @return array
     */
    public function getFinanceQuoteData()
    {
        $product = Mage::helper('rockar_catalog/vehicle')->getActiveVehicle();
        $helper = Mage::helper('financing_options');

        $savedFinanceData = $helper->getSavedFinanceData();

        $accessories = Mage::helper('rockar_accessories')->getSelectedAccessoriesPerProduct($product->getId());

        $financeData = Mage::helper('peppermint_financingoptions/finance_quote')->getFinanceQuoteData(
            $product,
            $savedFinanceData,
            $accessories,
            Rockar_FinancingOptions_Model_Calculation_Type_Abstract::CALC_TYPE_PDP
        );
        $financeData['payment_save_url'] = Mage::getUrl('financing/ajax/saveOnPdp');

        return $financeData;
    }
}

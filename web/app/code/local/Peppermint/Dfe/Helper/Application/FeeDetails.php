<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Dfe
 * @author    Lucian Mesaros <lucian.mesaros@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Dfe_Helper_Application_FeeDetails extends Mage_Core_Helper_Abstract
{
    /**
     * Set data for NEF details.
     *
     * @param  Peppermint_Sales_Model_Order $order
     * @return object
     */
    public function setFeeListData($order)
    {
        $data = $this->_prepareData($order);

        $feeList = new Peppermint_Dfe_Soap_Application_ArrayOfFeeDetails();
        $feeList[] = new Peppermint_Dfe_Soap_Application_FeeDetails($data['individual_fee_monthly'] ?? 0, 'ADMF1');
        $feeList[] = new Peppermint_Dfe_Soap_Application_FeeDetails($data['individual_fee_capitalised'] ?? 0, 'DOCF5');

        return $feeList;
    }

    /**
     * Prepare collection data by order.
     *
     * @param  Peppermint_Sales_Model_Order $order
     * @return []
     */
    protected function _prepareData($order)
    {
        $financeOverlayData = Mage::helper('rockar_checkout/order')->getFirstVisibleItem($order)
            ->getFinanceOverlay();

        $financeOverlay = Mage::helper('core')->jsonDecode($financeOverlayData);
        $financeData = [];

        if (!isset($financeOverlay['options'])) {
            $financeData['individual_fee_monthly'] = 0;
            $financeData['individual_fee_capitalised'] = 0;
        } else {
            foreach ($financeOverlay['options'] as $option) {
                foreach ($option['variables'] as ['variable' => $variableName, 'value' => $value]) {
                    if ($variableName == 'individual_fee_monthly' || $variableName == 'individual_fee_capitalised') {
                        $financeData[$variableName] = $value;
                    }
                }
            }
        }

        return $financeData;
    }
}

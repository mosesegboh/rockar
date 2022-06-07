<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Dfe
 * @author    Lucian Mesaros <lucian.mesaros@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Dfe_Helper_Application_NEFDetails extends Mage_Core_Helper_Abstract
{
    /**
     * Set data for NEF details.
     *
     * @param  Peppermint_Sales_Model_Order $order
     * @return object
     */
    public function setNefData($order)
    {
        $data = $this->_prepareData($order);

        return (new Peppermint_Dfe_Soap_Application_NEFDetails())->setSettlementValue($data['settlement_value'])
            ->setTradeInValue($data['trade_in_value'])
            ->setShortfallAllowance($data['shortfall_allowance'])
            ->setNEFAmt($data['nef_amt'])
            ->setModel($data['model'])
            ->setYear($data['year']);
    }

    /**
     * Prepare collection data by order.
     *
     * @param  Peppermint_Sales_Model_Order $order
     * @return []
     */
    protected function _prepareData($order)
    {
        $partExchange = Mage::getModel('rockar_partexchange/order')->load($order->getId(), 'order_id')
            ->getData();

        $orderItemData = Mage::helper('rockar_checkout/order')->getFirstVisibleItem($order)
            ->getData();

        $financeOverlay = Mage::helper('core')->jsonDecode($orderItemData['finance_overlay']);
        $financeData = [
            'px_settlement_creditamount' => 0,
            'shortfall_applied' => 0
        ];

        if (isset($financeOverlay['options'])) {
            foreach ($financeOverlay['options'] as $option) {
                foreach ($option['variables'] as ['variable' => $varName, 'value' => $value]) {
                    if (isset($financeData[$varName])) {
                        $financeData[$varName] = $value;
                    }
                }
            }
        }

        return [
            'settlement_value' => $partExchange['outstanding_finance'] ?? 0,
            'trade_in_value' => $partExchange['part_exchange_value'] ?? 0,
            'shortfall_allowance' => $financeData['shortfall_applied'],
            'model' => $partExchange['car_model'] ?? '',
            'nef_amt' => $financeData['px_settlement_creditamount'],
            'year' => $partExchange['car_year'] ?? ''
        ];
    }
}

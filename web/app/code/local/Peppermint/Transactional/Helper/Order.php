<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Transactional
 * @author    Donald Mailula <mailula.donald@partner.bmw.co.za>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Transactional_Helper_Order extends Peppermint_Transactional_Helper_Abstract
{
    /**
     * Get JSON Data for sending to the abstract API call
     * @return array
     */
    public function getSapData()
    {
        $order = $this->getOrderData();
        $orderData = $this->_prepareOrderData($order);
        $customerGcdmId = Mage::getModel('peppermint_gcdm/customer_access')->load($order->getCustomerId())
            ->getGcid() ?? '';

        return [
            'transactionType' => 'ZDS8',
            'gcdmId' => $customerGcdmId,
            'status' => 'E0001',
            'processingMode' => $order->getProcessingMode() ?? '',
            'productId' => $this->_getVistaOrderNumber($orderData['productId']),
            'campaignId' => $this->_getCampaignCookie(),
            'transactionId' => '',
            'dealerCode' => strstr($order->getDealerCode(), '_', true) ?: 0,
            'dspWebStoreId' => $orderData['storeId'] ?? 0,
            'externalId' => $order->getId() ?? 0,
            'userid' => $order->getDealerId() ?? '',
            'dateList' => [[
                'dateFrom' => '',
                'dateTo' => '',
                'technicalName' => '',
                'timeFrom' => '',
                'timeTo' => ''
            ]]
        ];
    }

    /**
     * Prepare Order data to be send to Transactional API
     *
     * @param Peppermint_Sales_Model_Order $order
     *
     * @return array
     */
    protected function _prepareOrderData($order)
    {
        $result = [];
        $orderItem = Mage::helper('rockar_checkout/order')->getFirstSimpleOrderItem($order);

        if ($orderItem) {
            $result = [
                'productId' => $orderItem->getProductId(),
                'storeId' => $orderItem->getStoreId()
            ];
        }

        return $result;
    }
}

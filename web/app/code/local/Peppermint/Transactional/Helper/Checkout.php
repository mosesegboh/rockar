<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Transactional
 * @author    Donald Mailula <mailula.donald@partner.bmw.co.za>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Transactional_Helper_Checkout extends Peppermint_Transactional_Helper_Abstract
{
    /**
     * Get JSON Data for sending to the abstract API call
     * @return array
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getSapData()
    {
        $checkout = $this->getCheckoutData();
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        $dealerId = Mage::helper('peppermint_all')->getDealerId();

        if ($this->getIsLoggedIn()) {
            $customerAccess = Mage::getModel('peppermint_gcdm/customer_access')->load($customerId);
            return [
                'transactionType' => 'ZDS5',
                'gcdmId' => $customerAccess->getGcid() ?? '',
                'status' => 'E0001',
                'processingMode' => 'A',
                'productId' => $this->_getVistaOrderNumber($checkout->getOrderItem()->getProductId()),
                'campaignId' => $this->_getCampaignCookie(),
                'dspWebStoreId' => Mage::app()->getStore()->getStoreId() ?? 0,
                'transactionId' => $checkout->getItem()->getItemId() ?? 0,
                'dealerCode' => strstr($checkout->getItem()
                    ->getQuote()
                    ->getData()['dealer_code'],'_',true) ?? 0,
                'externalId' => $checkout->getItem()->getQuoteId() ?? 0,
                'userid' => $dealerId,
                'dateList' => [[
                    'dateFrom' => '',
                    'dateTo' => '',
                    'technicalName' => '',
                    'timeFrom' => '',
                    'timeTo' => ''
                ]]
            ];
        }

        return $this->_getSapError('customer not logged in');
    }
}

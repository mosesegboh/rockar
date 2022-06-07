<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Transactional
 * @author    Donald Mailula <mailula.donald@partner.bmw.co.za>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Transactional_Helper_TradeIn extends Peppermint_Transactional_Helper_Abstract
{
    /**
     * Get JSON Data for sending to the abstract API call
     * @return array|string[]
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getSapData()
    {        
        $partExchange = $this->getPartExchangeData();
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        $dealerId = Mage::helper('peppermint_all')->getDealerId();

        if ($this->getIsLoggedIn()) {
            $customerAccess = Mage::getModel('peppermint_gcdm/customer_access')->load($customerId);

            return [
                'transactionType' => 'ZDS7',
                'gcdmId' => $customerAccess->getGcid() ?? '',
                'status' => $partExchange->getCrmStatus() ?? '',
                'processingMode' => $partExchange->getProcessingMode() ?? '',
                'productId' => $partExchange->getCapId() ?? $partExchange->getCap()['capid'] ?? 0,
                'campaignId' => $this->_getCampaignCookie(),
                'transactionId' => '',
                'storeId' => Mage::app()->getStore()->getStoreId() ?? 0,
                'dspWebStoreId' => Mage::app()->getStore()->getStoreId() ?? 0,
                'dealerCode' => '',
                'externalId' => $partExchange->getEntityId() ?? $partExchange->getPxId() ?? 0,
                'userid' => $dealerId,
                'dateList' => [[
                    'dateFrom' => '',
                    'dateTo' => '',
                    'technicalName' => '',
                    'timeFrom' => '',
                    'timeTo' => ''
                ]]
            ];
        } else {
            return $this->_getSapError('customer not logged in');
        }
    }
}

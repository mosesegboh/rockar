<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Transactional
 * @author    Donald Mailula <mailula.donald@partner.bmw.co.za>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Transactional_Helper_Registration extends Peppermint_Transactional_Helper_Abstract
{
    /**
     * Get JSON Data for sending to the abstract API call
     * @return array
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getSapData()
    {        
        $customer = $this->getCustomerData();
        $customerId = $customer->getId();
        $dealerId = Mage::helper('peppermint_all')->getDealerId();

        return [
            'transactionType' => 'ZDS1',
            'gcdmId' => $customer->getGcid() ?? '',
            'status' => 'E0001',
            'processingMode' => 'A',
            'productId' => '',
            'campaignId' => $this->_getCampaignCookie(),
            'dspWebStoreId' => Mage::app()->getStore()->getStoreId() ?? 0,
            'transactionId' => '',
            'dealerCode' => '',
            'externalId' => $customerId ?? 0,
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
}

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Transactional
 * @author    Donald Mailula <mailula.donald@partner.bmw.co.za>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Transactional_Helper_Login extends Peppermint_Transactional_Helper_Abstract
{
    /**
     * Get JSON Data for sending to the abstract API call
     * @return array
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getSapData()
    {
        $customerId = $this->getCustomerData()->getId();
        $customerAccess = Mage::getModel('peppermint_gcdm/customer_access')->load($customerId);
        $dealerId = Mage::helper('peppermint_all')->getDealerId();

        return [
            'transactionType' => 'ZDS2',
            'gcdmId' => $customerAccess->getGcid() ?? '',
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

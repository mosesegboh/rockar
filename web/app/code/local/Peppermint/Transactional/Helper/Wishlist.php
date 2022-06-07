<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Transactional
 * @author    Donald Mailula <mailula.donald@partner.bmw.co.za>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Transactional_Helper_Wishlist extends Peppermint_Transactional_Helper_Abstract
{
    /**
     * Get JSON Data for sending to the abstract API call
     * @return array|string[]
     * @throws Mage_Core_Model_Store_Exception
    */
    public function getSapData()
    {
        $wishlist = $this->getWishlistData();
        $customerId = $wishlist->getWishlist()->getCustomerId();
        $dealerId = Mage::helper('peppermint_all')->getDealerId();

        if ($this->getIsLoggedIn()) {
            $customerAccess = Mage::getModel('peppermint_gcdm/customer_access')->load($customerId);

            return [
                'transactionType' => 'ZDS3',
                'gcdmId' => $customerAccess->getGcid() ?? '',
                'status' => $wishlist->getCrmStatus() ?? '',
                'processingMode'=> $wishlist->getProcessingMode() ?? '',
                'productId' => $this->_getVistaOrderNumberFromWishlist($wishlist),
                'campaignId' => $this->_getCampaignCookie(),
                'transactionId' => '',
                'dspWebStoreId' => Mage::app()->getStore()->getStoreId() ?? 0,
                'dealerCode' => '',
                'externalId' => $wishlist->getWishlist()->getId() ?? $wishlist->getWishlist()->getWishlistId() ?? 0,
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

    /**
     * This method is required because when removing the vehicle the product object is
     * not available, so we need to use the helper method in that scenario.
     *
     * @param $wishlist event object,
     * @return string the vehicle vin number
     */
    protected function _getVistaOrderNumberFromWishlist($wishlist)
    {
        $tmpProduct = $wishlist->getProduct();

        return $tmpProduct
            ? $tmpProduct->getData('vista_order_number')
            : $this->_getVistaOrderNumber($wishlist->getItem()->getProductId());
    }
}

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_DealerEmail
 * @author    Bogdan Gafitescu <bogdan.gafitescu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_DealerEmail_Helper_Dealer extends Mage_Core_Helper_Abstract
{
    /**
     * Get dealer emails list.
     *
     * @param string $dealerCode
     * @return null|string
     */
    public function getDealerEmails($dealerCode)
    {
        return $this->getLocalStore($dealerCode)
            ->getStoreAddress()
            ->getEmailAddress();
    }

    /**
     * Get Local Store by dealer code.
     *
     * @param string $dealerCode
     * @return object
     */
    public function getLocalStore($dealerCode)
    {
        return Mage::getModel('rockar_localstores/stores')->load($dealerCode, 'code');
    }

    /**
     * Get Sales Emails section field config.
     *
     * @param string $node
     * @param integer $storeId
     * @return boolean
     */
    public function isDealerCopyEnabled($node, $storeId)
    {
        return Mage::getStoreConfigFlag('sales_email/' . $node . '/send_copy_to_dealer', $storeId);
    }
}

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Page
 * @author    Artis Viblo <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Page_Model_Observer
{
    /**
     * Wrapper function to apply Adobe Analytics Data
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Void
     */
    public function applyAnalyticsData($observer)
    {
        $html = $observer->getEvent()->getHtml();
        $this->applyGuid($html);
        $this->applyRequestParams($html);
    }

    /**
     * Add GCID Hash data to DOM.
     *
     * @param Varien_Object $html
     *
     * @return void
     */
    public function applyGuid($html)
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $gcdmCustomerAccess = Mage::getModel('peppermint_gcdm/customer_access')->load($customer->getId());
            $cryptedGcid = '"' . $this->madeUuid(md5($gcdmCustomerAccess->getGcid())) . '"';
            $html->setHtml('<script type="text/javascript">var hashedGcid = ' . $cryptedGcid . '</script>');
        }
    }

    /**
     * Apply Url Referrer
     *
     * @param Varien_Object $html
     *
     * @return void
     */
    public function applyRequestParams($html)
    {
        $toInsert = Mage::helper('peppermint_page')->getRequestParams();
        $toInsert = (is_null($toInsert) || $toInsert == '' ? '""' : $toInsert) .';';
        $html->setHtml($html->getHtml() .
            '<script type="text/javascript"> const urlParams = ' . $toInsert . '</script>'
        );
    }

    /**
     * Generate UUID string from crypted hash.
     *
     * @param $cryptedGcid
     *
     * @return string
     */
    public function madeUuid($cryptedGcid)
    {
        return substr($cryptedGcid, 0, 8) . '-' .
            substr($cryptedGcid, 8, 4) . '-' .
            substr($cryptedGcid, 12, 4) . '-' .
            substr($cryptedGcid, 16, 4) . '-' .
            substr($cryptedGcid, 20);
    }
}
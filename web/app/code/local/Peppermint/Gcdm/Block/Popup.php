<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Gcdm
 * @author    Sergejs Plisko <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Gcdm_Block_Popup extends Mage_Core_Block_Template
{
    /**
     * Get customer session
     *
     * @return Mage_Core_Model_Abstract
     */
    public function getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Get customer from session
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        return $this->getCustomerSession()->getCustomer();
    }

    /**
     * Get list of checkboxes from session
     *
     * @return string
     */
    public function getPolicyCheckboxes()
    {
       return Mage::helper('peppermint_gcdm/data')->getCustomerPolicyCheckboxes();
    }

    /**
     * Show activation popup if GCDM customer status is not active
     *
     * @return boolean
     */
    public function isShowActivationPopup()
    {
        if ($this->getCustomerSession()->isLoggedIn() ) {
            return !Mage::helper('peppermint_gcdm/popup')->isCustomerActivated();
        }

        return false;
    }

    /**
     * Show account missing details popup
     *
     * @return boolean
     */
    protected function isShowMissingDetailsPopup()
    {
        if (
            $this->getCustomerSession()->isLoggedIn()
            && $this->getCustomerSession()
                ->getData(Peppermint_Gcdm_Helper_Popup::CUSTOMER_ACTIVETED_SESS_KEY)
        ) {
            $details = $this->getCustomerDetails();

            return (!$details['surname'] || !$details['givenName'] || !$details['salutation']);
        }

        return false;
    }

    /**
     * Get customer mandatory details
     *
     * @return array
     */
    public function getCustomerDetails()
    {
        if ($this->getCustomerSession()->isLoggedIn()) {
            return $this->getCustomerSession()
                ->getData(Peppermint_Gcdm_Helper_Data::GCDM_CUSTOMER_MANDATORY_DETAILS_SESSION_KEY);
        }

        return [];
    }

    /**
     * Returns form action and customer parameter
     *
     * @return string
     */
    public function getFormAction()
    {
        return $this->getUrl(
            'gcdm/popup/apply',
            [
                '_secure' => $this->_isSecure()
            ]
        );
    }

    /**
     * Show policy popup if Customer has an out of date/nonexistent policy
     *
     * @return boolean
     */
    protected function isShowPolicyPopup()
    {
        if (
            $this->getCustomerSession()->isLoggedIn()
            && $this->getCustomerSession()
                ->getData(Peppermint_Gcdm_Helper_Popup::CUSTOMER_ACTIVETED_SESS_KEY)
            && false === $this->getCustomerSession()
                ->getData(Peppermint_Gcdm_Helper_Data::GCDM_LATEST_POLICY_ACCEPTED_SESSION_KEY)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Get JSON encoded list of checkboxes
     *
     * @return string
     */
    protected function getJsonCheckboxes()
    {
        return Mage::helper('rockar_all')->jsonEncode($this->getPolicyCheckboxes());
    }

}

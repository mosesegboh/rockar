<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Dfe
 * @author    Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Dfe_Helper_Application_ContactDetails extends Mage_Core_Helper_Abstract
{
    /**
     * Set data for contact details.
     *
     * @param  integer $orderId
     * @return object
     */
    public function setContactDetailsData($orderId)
    {
        $data = $this->_prepareData($orderId);

        return (new Peppermint_Dfe_Soap_Application_ContactDetails())->setPrefMethodContactEmail($data['pref_method_contact_email'])
            ->setPrefMethodContactSMS($data['pref_method_contact_sms'])
            ->setPrefMethodContactNormal($data['pref_method_contact_normal'])
            ->setContractDoc($data['contract_document'])
            ->setStatementFreq($data['statement_frequency']);
    }

    /**
     * Prepare collection data by orderID.
     *
     * @param  integer $orderId
     * @return []
     */
    protected function _prepareData($orderId)
    {
        $data = Mage::getModel('rockar_checkout/order_additional')->load($orderId, 'order_id');

        return [
            'pref_method_contact_email' => $data['pref_method_contact_email'] ?? false,
            'pref_method_contact_sms' => $data['pref_method_contact_sms'] ?? false,
            'pref_method_contact_normal' => $data['pref_method_contact_normal'] ?? false,
            'contract_document' => $data['contract_document'] ?? 0,
            'statement_frequency' => $data['statement_frequency'] ?? 0
        ];
    }
}

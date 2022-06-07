<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Dfe
 * @author    Robert Ionas <robert.ionas@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Dfe_Helper_Application_BankDetails extends Mage_Core_Helper_Abstract
{
    /**
     * Set data for bank details.
     *
     * @param  integer $orderId
     * @return object
     */
    public function setBankDetailsData($orderId)
    {
        $data = Mage::getModel('rockar_checkout/order_additional')->load($orderId, 'order_id')
            ->getData();

        return (new Peppermint_Dfe_Soap_Application_BankDetails())
            ->setAccountHolderName($data['name_of_bank_account'] ?? '')
            ->setAccountTypeCode($data['account_type_code'] ?? '')
            ->setBankName($data['bank_name'] ?? '')
            ->setBranchName($data['branch_name'] ?? '')
            ->setAccountNumber($data['account_number'] ?? '')
            ->setBranchCode($data['branch_code'] ?? '');
    }
}

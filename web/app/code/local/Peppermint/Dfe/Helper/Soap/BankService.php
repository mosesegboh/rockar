<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Dfe
 * @author    Robert Ionas <robert.ionas@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Dfe_Helper_Soap_BankService extends Peppermint_Dfe_Helper_Soap_Abstract_Service
{
    const XMLNS = 'http://tempuri.org/';

    /**
     * @var [] The defined classes
     */
    protected static $_classmap = [
        'GetBankList' => 'Peppermint_Dfe_Soap_Bank_GetBankList',
        'GetBankListResponse' => 'Peppermint_Dfe_Soap_Bank_GetBankListResponse',
        'ArrayOfBank' => 'Peppermint_Dfe_Soap_Bank_ArrayOfBank',
        'Bank' => 'Peppermint_Dfe_Soap_Bank_Bank'
    ];

    /**
     * Make the soap call.
     *
     * @param Peppermint_Dfe_Soap_Bank_GetBankList $parameters
     * @return Peppermint_Dfe_Soap_Bank_GetBankListResponse
     */
    public function getBank(Peppermint_Dfe_Soap_Bank_GetBankList $parameters)
    {
        return $this->__soapCall('GetBankList', [$parameters]);
    }
}

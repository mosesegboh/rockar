<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Dfe
 * @author    Robert Ionas <robert.ionas@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Dfe_Helper_Soap_BankBranchService extends Peppermint_Dfe_Helper_Soap_Abstract_Service
{
    /**
     * @var [] The defined classes
     */
    protected static $_classmap = [
        'GetBankBranchList' => 'Peppermint_Dfe_Soap_BankBranch_GetBankBranchList',
        'GetBankBranchListResponse' => 'Peppermint_Dfe_Soap_BankBranch_GetBankBranchListResponse',
        'BankBranchCode' => 'Peppermint_Dfe_Soap_BankBranch_BankBranchCode',
        'ArrayOfBankBranchCode' => 'Peppermint_Dfe_Soap_BankBranch_ArrayOfBankBranchCode'
    ];

    /**
     * Make the soap call.
     *
     * @param Peppermint_Dfe_Soap_BankBranch_GetBankBranchList $parameters
     * @return Peppermint_Dfe_Soap_BankBranch_GetBankBranchListResponse
     */
    public function getBankBranch(Peppermint_Dfe_Soap_BankBranch_GetBankBranchList $parameters)
    {
        return $this->__soapCall('GetBankBranchList', [$parameters]);
    }
}

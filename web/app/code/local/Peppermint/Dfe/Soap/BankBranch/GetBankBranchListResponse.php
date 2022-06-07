<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Robert Ionas <robert.ionas@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_BankBranch_GetBankBranchListResponse
{
    /**
     * @var Peppermint_Dfe_Soap_BankBranch_ArrayOfBankBranchCode
     */
    protected $GetBankBranchListResult;

    /**
     * @param Peppermint_Dfe_Soap_BankBranch_ArrayOfBankBranchCode $getBankBranchListResult
     */
    public function __construct($getBankBranchListResult)
    {
        $this->GetBankBranchListResult = $getBankBranchListResult;
    }

    /**
     * @return Peppermint_Dfe_Soap_BankBranch_ArrayOfBankBranchCode
     */
    public function getGetBankBranchListResult()
    {
        return $this->GetBankBranchListResult;
    }

    /**
     * @param Peppermint_Dfe_Soap_BankBranch_ArrayOfBankBranchCode $getBankBranchListResult
     * @return Peppermint_Dfe_Soap_BankBranch_GetBankBranchListResponse
     */
    public function setGetBankBranchListResult($getBankBranchListResult)
    {
        $this->GetBankBranchListResult = $getBankBranchListResult;

        return $this;
    }
}

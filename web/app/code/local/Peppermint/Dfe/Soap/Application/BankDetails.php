<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_Application_BankDetails
{
    /**
     * @var string $AccountHolderName
     */
    protected $AccountHolderName = null;

    /**
     * @var string $AccountTypeCode
     */
    protected $AccountTypeCode = null;

    /**
     * @var string $BankName
     */
    protected $BankName = null;

    /**
     * @var string $BranchName
     */
    protected $BranchName = null;

    /**
     * @var string $AccountNumber
     */
    protected $AccountNumber = null;

    /**
     * @var string $BranchCode
     */
    protected $BranchCode = null;

    /**
     * @return string
     */
    public function getAccountHolderName()
    {
        return $this->AccountHolderName;
    }

    /**
     * @param string $accountHolderName
     * @return Peppermint_Dfe_Soap_Application_BankDetails
     */
    public function setAccountHolderName($accountHolderName)
    {
        $this->AccountHolderName = $accountHolderName;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccountTypeCode()
    {
        return $this->AccountTypeCode;
    }

    /**
     * @param string $accountTypeCode
     * @return Peppermint_Dfe_Soap_Application_BankDetails
     */
    public function setAccountTypeCode($accountTypeCode)
    {
        $this->AccountTypeCode = $accountTypeCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getBankName()
    {
        return $this->BankName;
    }

    /**
     * @param string $bankName
     * @return Peppermint_Dfe_Soap_Application_BankDetails
     */
    public function setBankName($bankName)
    {
        $this->BankName = $bankName;

        return $this;
    }

    /**
     * @return string
     */
    public function getBranchName()
    {
        return $this->BranchName;
    }

    /**
     * @param string $branchName
     * @return Peppermint_Dfe_Soap_Application_BankDetails
     */
    public function setBranchName($branchName)
    {
        $this->BranchName = $branchName;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccountNumber()
    {
        return $this->AccountNumber;
    }

    /**
     * @param string $accountNumber
     * @return Peppermint_Dfe_Soap_Application_BankDetails
     */
    public function setAccountNumber($accountNumber)
    {
        $this->AccountNumber = $accountNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getBranchCode()
    {
        return $this->BranchCode;
    }

    /**
     * @param string $branchCode
     * @return Peppermint_Dfe_Soap_Application_BankDetails
     */
    public function setBranchCode($branchCode)
    {
        $this->BranchCode = $branchCode;

        return $this;
    }
}

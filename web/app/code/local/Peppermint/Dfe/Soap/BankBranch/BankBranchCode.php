<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Dfe
 * @author    Robert Ionas <robert.ionas@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Dfe_Soap_BankBranch_BankBranchCode
{
    /**
     * @var string $BankName
     */
    protected $BankName;

    /**
     * @var string $BankCode
     */
    protected $BankCode;

    /**
     * @var string $BranchCode
     */
    protected $BranchCode;

    /**
     * @var string $BranchName
     */
    protected $BranchName;

    /**
     * @return string
     */
    public function getBankName()
    {
        return $this->BankName;
    }

    /**
     * @param string $bankName
     *
     * @return Peppermint_Dfe_Soap_BankBranch_BankBranchCode $this
     */
    public function setBankName($bankName)
    {
        $this->BankName = $bankName;

        return $this;
    }

    /**
     * @return string
     */
    public function getBankCode()
    {
        return $this->BankCode;
    }

    /**
     * @param string $bankCode
     *
     * @return Peppermint_Dfe_Soap_BankBranch_BankBranchCode $this
     */
    public function setBankCode($bankCode)
    {
        $this->BankCode = $bankCode;

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
     *
     * @return Peppermint_Dfe_Soap_BankBranch_BankBranchCode $this
     */
    public function setBranchCode($branchCode)
    {
        $this->BranchCode = $branchCode;

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
     *
     * @return Peppermint_Dfe_Soap_BankBranch_BankBranchCode $this
     */
    public function setBranchName($branchName)
    {
        $this->BranchName = $branchName;

        return $this;
    }

    /**
     * Prepares the data as model resource expects it
     *
     * @return array
     */
    public function getData()
    {
        return [
            'bank_name' => $this->getBankName(),
            'bank_code' => $this->getBankCode(),
            'branch_code' => $this->getBranchCode(),
            'branch_name' => $this->getBranchName()
        ];
    }
}

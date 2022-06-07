<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Robert Ionas <robert.ionas@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_Bank_Bank
{
    /**
     * @var string $BankName
     */
    protected $BankName = null;

    /**
     * @var string $BankCode
     */
    protected $BankCode = null;

    /**
     * @return string
     */
    public function getBankName()
    {
        return $this->BankName;
    }

    /**
     * @param string $bankName
     * @return Peppermint_Dfe_Soap_Bank_Bank
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
     * @return Peppermint_Dfe_Soap_Bank_Bank
     */
    public function setBankCode($bankCode)
    {
        $this->BankCode = $bankCode;

        return $this;
    }
}

<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Robert Ionas <robert.ionas@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_Bank_GetBankListResponse
{
    /**
     * @var Peppermint_Dfe_Soap_Bank_ArrayOfBank
     */
    protected $GetBankListResult;

    /**
     * @param Peppermint_Dfe_Soap_Bank_ArrayOfBank $getBankListResult
     */
    public function __construct($getBankListResult)
    {
        $this->GetBankListResult = $getBankListResult;
    }

    /**
     * @return Peppermint_Dfe_Soap_Bank_ArrayOfBank
     */
    public function getGetBankListResult()
    {
        return $this->GetBankListResult;
    }

    /**
     * @param Peppermint_Dfe_Soap_Bank_ArrayOfBank $getBankListResult
     * @return Peppermint_Dfe_Soap_Bank_GetBankListResponse
     */
    public function setGetBankListResult($getBankListResult)
    {
        $this->GetBankListResult = $getBankListResult;

        return $this;
    }
}

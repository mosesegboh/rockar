<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_Application_SubmitApplicationResponse
{
    /**
     * @var Peppermint_Dfe_Soap_Application_FinanceApplicationResponse $SubmitApplicationResult
     */
    protected $SubmitApplicationResult = null;

    /**
     * @param Peppermint_Dfe_Soap_Application_FinanceApplicationResponse $submitApplicationResult
     */
    public function __construct($submitApplicationResult)
    {
        $this->SubmitApplicationResult = $submitApplicationResult;
    }

    /**
     * @return Peppermint_Dfe_Soap_Application_FinanceApplicationResponse
     */
    public function getSubmitApplicationResult()
    {
        return $this->SubmitApplicationResult;
    }

    /**
     * @param Peppermint_Dfe_Soap_Application_FinanceApplicationResponse $submitApplicationResult
     * @return Peppermint_Dfe_Soap_Application_SubmitApplicationResponse
     */
    public function setSubmitApplicationResult($submitApplicationResult)
    {
        $this->SubmitApplicationResult = $submitApplicationResult;

        return $this;
    }
}

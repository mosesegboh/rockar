<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_Application_QuoteDetails
{
    /**
     * @var string $QuoteReferenceId
     */
    protected $QuoteReferenceId = null;

    /**
     * @var Peppermint_Dfe_Soap_Application_FinanceDetails $FinanceDetails
     */
    protected $FinanceDetails = null;

    /**
     * @var Peppermint_Dfe_Soap_Application_AssetDetails $AssetDetails
     */
    protected $AssetDetails = null;

    /**
     * @var Peppermint_Dfe_Soap_Application_ArrayOfFeeDetails $FeeList
     */
    protected $FeeList = null;

    /**
     * @var Peppermint_Dfe_Soap_Application_ArrayOfNEFDetails $NEFList
     */
    protected $NEFList = null;

    /**
     * @return string
     */
    public function getQuoteReferenceId()
    {
        return $this->QuoteReferenceId;
    }

    /**
     * @param string $quoteReferenceId
     * @return Peppermint_Dfe_Soap_Application_QuoteDetails
     */
    public function setQuoteReferenceId($quoteReferenceId)
    {
        $this->QuoteReferenceId = $quoteReferenceId;

        return $this;
    }

    /**
     * @return Peppermint_Dfe_Soap_Application_FinanceDetails
     */
    public function getFinanceDetails()
    {
        return $this->FinanceDetails;
    }

    /**
     * @param Peppermint_Dfe_Soap_Application_FinanceDetails $financeDetails
     * @return Peppermint_Dfe_Soap_Application_QuoteDetails
     */
    public function setFinanceDetails($financeDetails)
    {
        $this->FinanceDetails = $financeDetails;

        return $this;
    }

    /**
     * @return Peppermint_Dfe_Soap_Application_AssetDetails
     */
    public function getAssetDetails()
    {
        return $this->AssetDetails;
    }

    /**
     * @param Peppermint_Dfe_Soap_Application_AssetDetails $assetDetails
     * @return Peppermint_Dfe_Soap_Application_QuoteDetails
     */
    public function setAssetDetails($assetDetails)
    {
        $this->AssetDetails = $assetDetails;

        return $this;
    }

    /**
     * @return Peppermint_Dfe_Soap_Application_ArrayOfFeeDetails
     */
    public function getFeeList()
    {
        return $this->FeeList;
    }

    /**
     * @param Peppermint_Dfe_Soap_Application_ArrayOfFeeDetails $feeList
     * @return Peppermint_Dfe_Soap_Application_QuoteDetails
     */
    public function setFeeList($feeList)
    {
        $this->FeeList = $feeList;

        return $this;
    }

    /**
     * @return Peppermint_Dfe_Soap_Application_ArrayOfNEFDetails
     */
    public function getNefList()
    {
        return $this->NEFList;
    }

    /**
     * @param Peppermint_Dfe_Soap_Application_ArrayOfNEFDetails $nefList
     * @return Peppermint_Dfe_Soap_Application_QuoteDetails
     */
    public function setNefList($nefList)
    {
        $this->NEFList = $nefList;

        return $this;
    }
}

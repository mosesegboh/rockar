<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_Application_FinanceApplication
{
    /**
     * @var string $RequestId
     */
    protected $RequestId = null;

    /**
     * @var int $ApplicationId
     */
    protected $ApplicationId = null;

    /**
     * @var string $ApplicationDate
     */
    protected $ApplicationDate = null;

    /**
     * @var int $OutletID
     */
    protected $OutletID = null;

    /**
     * @var string $DealerID
     */
    protected $DealerID = null;

    /**
     * @var string $UserID
     */
    protected $UserID = null;

    /**
     * @var string $FnIemailAddress
     */
    protected $FnIemailAddress = null;

    /**
     * @var string $ServiceProvider
     */
    protected $ServiceProvider = null;

    /**
     * @var boolean $SettleExistingInstallment
     */
    protected $SettleExistingInstallment = null;

    /**
     * @var string $SettlementBankName
     */
    protected $SettlementBankName = null;

    /**
     * @var string $SettlementAccountNumber
     */
    protected $SettlementAccountNumber = null;

    /**
     * @var float $SettlementAmount
     */
    protected $SettlementAmount = null;

    /**
     * @var float $MonthlyInstallment
     */
    protected $MonthlyInstallment = null;

    /**
     * @var Peppermint_Dfe_Soap_Application_IndividualCustomerDetails[] $IndividualCustomerList
     */
    protected $IndividualCustomerList = null;

    /**
     * @var Peppermint_Dfe_Soap_Application_CorporateCustomer[] $CorporateCustomerList
     */
    protected $CorporateCustomerList = null;

    /**
     * @var Peppermint_Dfe_Soap_Application_QuoteDetails[] $QuoteList
     */
    protected $QuoteList = null;

    /**
     * @param int $outletID
     * @param boolean $settleExistingInstallment
     */
    public function __construct($outletID, $settleExistingInstallment)
    {
        $this->OutletID = $outletID;
        $this->SettleExistingInstallment = $settleExistingInstallment;
    }

    /**
     * @return string
     */
    public function getRequestId()
    {
        return $this->RequestId;
    }

    /**
     * @param string $requestId
     * @return Peppermint_Dfe_Soap_Application_FinanceApplication
     */
    public function setRequestId($requestId)
    {
        $this->RequestId = $requestId;

        return $this;
    }

    /**
     * @return int
     */
    public function getApplicationId()
    {
        return $this->ApplicationId;
    }

    /**
     * @param int $applicationId
     * @return Peppermint_Dfe_Soap_Application_FinanceApplication
     */
    public function setApplicationId($applicationId)
    {
        $this->ApplicationId = $applicationId;

        return $this;
    }

    /**
     * @return string
     */
    public function getApplicationDate()
    {
        return $this->ApplicationDate;
    }

    /**
     * @param string $applicationDate
     * @return Peppermint_Dfe_Soap_Application_FinanceApplication
     */
    public function setApplicationDate($applicationDate)
    {
        $this->ApplicationDate = $applicationDate;

        return $this;
    }

    /**
     * @return int
     */
    public function getOutletID()
    {
        return $this->OutletID;
    }

    /**
     * @param int $outletID
     * @return Peppermint_Dfe_Soap_Application_FinanceApplication
     */
    public function setOutletID($outletID)
    {
        $this->OutletID = $outletID;

        return $this;
    }

    /**
     * @return string
     */
    public function getDealerID()
    {
        return $this->DealerID;
    }

    /**
     * @param string $dealerID
     * @return Peppermint_Dfe_Soap_Application_FinanceApplication
     */
    public function setDealerId($dealerID)
    {
        $this->DealerID = $dealerID;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->UserID;
    }

    /**
     * @param string $userID
     * @return Peppermint_Dfe_Soap_Application_FinanceApplication
     */
    public function setUserId($userID)
    {
        $this->UserID = $userID;

        return $this;
    }

    /**
     * @return string
     */
    public function getFnIemailAddress()
    {
        return $this->FnIemailAddress;
    }

    /**
     * @param string $fnIemailAddress
     * @return Peppermint_Dfe_Soap_Application_FinanceApplication
     */
    public function setFnIemailAddress($fnIemailAddress)
    {
        $this->FnIemailAddress = $fnIemailAddress;

        return $this;
    }

    /**
     * @return string
     */
    public function getServiceProvider()
    {
        return $this->ServiceProvider;
    }

    /**
     * @param string $serviceProvider
     * @return Peppermint_Dfe_Soap_Application_FinanceApplication
     */
    public function setServiceProvider($serviceProvider)
    {
        $this->ServiceProvider = $serviceProvider;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getSettleExistingInstallment()
    {
        return $this->SettleExistingInstallment;
    }

    /**
     * @param boolean $settleExistingInstallment
     * @return Peppermint_Dfe_Soap_Application_FinanceApplication
     */
    public function setSettleExistingInstallment($settleExistingInstallment)
    {
        $this->SettleExistingInstallment = $settleExistingInstallment;

        return $this;
    }

    /**
     * @return string
     */
    public function getSettlementBankName()
    {
        return $this->SettlementBankName;
    }

    /**
     * @param string $settlementBankName
     * @return Peppermint_Dfe_Soap_Application_FinanceApplication
     */
    public function setSettlementBankName($settlementBankName)
    {
        $this->SettlementBankName = $settlementBankName;

        return $this;
    }

    /**
     * @return string
     */
    public function getSettlementAccountNumber()
    {
        return $this->SettlementAccountNumber;
    }

    /**
     * @param string $settlementAccountNumber
     * @return Peppermint_Dfe_Soap_Application_FinanceApplication
     */
    public function setSettlementAccountNumber($settlementAccountNumber)
    {
        $this->SettlementAccountNumber = $settlementAccountNumber;

        return $this;
    }

    /**
     * @return float
     */
    public function getSettlementAmount()
    {
        return $this->SettlementAmount;
    }

    /**
     * @param float $settlementAmount
     * @return Peppermint_Dfe_Soap_Application_FinanceApplication
     */
    public function setSettlementAmount($settlementAmount)
    {
        $this->SettlementAmount = $settlementAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getMonthlyInstallment()
    {
        return $this->MonthlyInstallment;
    }

    /**
     * @param float $monthlyInstallment
     * @return Peppermint_Dfe_Soap_Application_FinanceApplication
     */
    public function setMonthlyInstallment($monthlyInstallment)
    {
        $this->MonthlyInstallment = $monthlyInstallment;

        return $this;
    }

    /**
     * @return Peppermint_Dfe_Soap_Application_IndividualCustomerDetails[]
     */
    public function getIndividualCustomerList()
    {
        return $this->IndividualCustomerList;
    }

    /**
     * @param Peppermint_Dfe_Soap_Application_IndividualCustomerDetails[] $individualCustomerList
     * @return Peppermint_Dfe_Soap_Application_FinanceApplication
     */
    public function setIndividualCustomerList(array $individualCustomerList = null)
    {
        $this->IndividualCustomerList = $individualCustomerList;

        return $this;
    }

    /**
     * @return Peppermint_Dfe_Soap_Application_CorporateCustomer[]
     */
    public function getCorporateCustomerList()
    {
        return $this->CorporateCustomerList;
    }

    /**
     * @param Peppermint_Dfe_Soap_Application_CorporateCustomer[] $corporateCustomerList
     * @return Peppermint_Dfe_Soap_Application_FinanceApplication
     */
    public function setCorporateCustomerList(array $corporateCustomerList = null)
    {
        $this->CorporateCustomerList = $corporateCustomerList;

        return $this;
    }

    /**
     * @return Peppermint_Dfe_Soap_Application_QuoteDetails[]
     */
    public function getQuoteList()
    {
        return $this->QuoteList;
    }

    /**
     * @param Peppermint_Dfe_Soap_Application_QuoteDetails[] $quoteList
     * @return Peppermint_Dfe_Soap_Application_FinanceApplication
     */
    public function setQuoteList(array $quoteList = null)
    {
        $this->QuoteList = $quoteList;

        return $this;
    }
}

<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_Application_FinanceDetails
{
    /**
     * @var string $QuoteDate
     */
    protected $QuoteDate = null;

    /**
     * @var string $SubProductID
     */
    protected $SubProductID = null;

    /**
     * @var float $Deposit
     */
    protected $Deposit = null;

    /**
     * @var float $ClientInterestRate
     */
    protected $ClientInterestRate = null;

    /**
     * @var float $DealerContributionAmount
     */
    protected $DealerContributionAmount = null;

    /**
     * @var float $ResidualValue
     */
    protected $ResidualValue = null;

    /**
     * @var int $TenureMonths
     */
    protected $TenureMonths = null;

    /**
     * @var int $Kilometer
     */
    protected $Kilometer = null;

    /**
     * @var float $RetailPrice
     */
    protected $RetailPrice = null;

    /**
     * @var float $DiscountAmount
     */
    protected $DiscountAmount = null;

    /**
     * @var string $CashDepositSourceCode
     */
    protected $CashDepositSourceCode = null;

    /**
     * @var string $CashDepositOtherDetails
     */
    protected $CashDepositOtherDetails = null;

    /**
     * @param int $tenureMonths
     * @param int $kilometer
     * @param float $retailPrice
     */
    public function __construct($tenureMonths, $kilometer, $retailPrice)
    {
        $this->TenureMonths = $tenureMonths;
        $this->Kilometer = $kilometer;
        $this->RetailPrice = $retailPrice;
    }

    /**
     * @return string
     */
    public function getQuoteDate()
    {
        return $this->QuoteDate;
    }

    /**
     * @param string $quoteDate
     * @return Peppermint_Dfe_Soap_Application_FinanceDetails
     */
    public function setQuoteDate($quoteDate)
    {
        $this->QuoteDate = $quoteDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubProductID()
    {
        return $this->SubProductID;
    }

    /**
     * @param string $subProductID
     * @return Peppermint_Dfe_Soap_Application_FinanceDetails
     */
    public function setSubProductID($subProductID)
    {
        $this->SubProductID = $subProductID;

        return $this;
    }

    /**
     * @return float
     */
    public function getDeposit()
    {
        return $this->Deposit;
    }

    /**
     * @param float $deposit
     * @return Peppermint_Dfe_Soap_Application_FinanceDetails
     */
    public function setDeposit($deposit)
    {
        $this->Deposit = $deposit;

        return $this;
    }

    /**
     * @return float
     */
    public function getClientInterestRate()
    {
        return $this->ClientInterestRate;
    }

    /**
     * @param float $clientInterestRate
     * @return Peppermint_Dfe_Soap_Application_FinanceDetails
     */
    public function setClientInterestRate($clientInterestRate)
    {
        $this->ClientInterestRate = $clientInterestRate;

        return $this;
    }

    /**
     * @return float
     */
    public function getDealerContributionAmount()
    {
        return $this->DealerContributionAmount;
    }

    /**
     * @param float $dealerContributionAmount
     * @return Peppermint_Dfe_Soap_Application_FinanceDetails
     */
    public function setDealerContributionAmount($dealerContributionAmount)
    {
        $this->DealerContributionAmount = $dealerContributionAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getResidualValue()
    {
        return $this->ResidualValue;
    }

    /**
     * @param float $residualValue
     * @return Peppermint_Dfe_Soap_Application_FinanceDetails
     */
    public function setResidualValue($residualValue)
    {
        $this->ResidualValue = $residualValue;

        return $this;
    }

    /**
     * @return int
     */
    public function getTenureMonths()
    {
        return $this->TenureMonths;
    }

    /**
     * @param int $tenureMonths
     * @return Peppermint_Dfe_Soap_Application_FinanceDetails
     */
    public function setTenureMonths($tenureMonths)
    {
        $this->TenureMonths = $tenureMonths;

        return $this;
    }

    /**
     * @return int
     */
    public function getKilometer()
    {
        return $this->Kilometer;
    }

    /**
     * @param int $kilometer
     * @return Peppermint_Dfe_Soap_Application_FinanceDetails
     */
    public function setKilometer($kilometer)
    {
        $this->Kilometer = $kilometer;

        return $this;
    }

    /**
     * @return float
     */
    public function getRetailPrice()
    {
        return $this->RetailPrice;
    }

    /**
     * @param float $retailPrice
     * @return Peppermint_Dfe_Soap_Application_FinanceDetails
     */
    public function setRetailPrice($retailPrice)
    {
        $this->RetailPrice = $retailPrice;

        return $this;
    }

    /**
     * @return float
     */
    public function getDiscountAmount()
    {
        return $this->DiscountAmount;
    }

    /**
     * @param float $discountAmount
     * @return Peppermint_Dfe_Soap_Application_FinanceDetails
     */
    public function setDiscountAmount($discountAmount)
    {
        $this->DiscountAmount = $discountAmount;

        return $this;
    }

    /**
     * @return string
     */
    public function getCashDepositSourceCode()
    {
        return $this->CashDepositSourceCode;
    }

    /**
     * @param string $cashDepositSourceCode
     * @return Peppermint_Dfe_Soap_Application_FinanceDetails
     */
    public function setCashDepositSourceCode($cashDepositSourceCode)
    {
        $this->CashDepositSourceCode = $cashDepositSourceCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getCashDepositOtherDetails()
    {
        return $this->CashDepositOtherDetails;
    }

    /**
     * @param string $cashDepositOtherDetails
     * @return Peppermint_Dfe_Soap_Application_FinanceDetails
     */
    public function setCashDepositOtherDetails($cashDepositOtherDetails)
    {
        $this->CashDepositOtherDetails = $cashDepositOtherDetails;

        return $this;
    }
}

<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_Application_IncomeDetails
{
    /**
     * @var float $MonthlyGrossSalary
     */
    protected $MonthlyGrossSalary = null;

    /**
     * @var float $CarAllowance
     */
    protected $CarAllowance = null;

    /**
     * @var float $TakeHomeSalary
     */
    protected $TakeHomeSalary = null;

    /**
     * @var float $AdditionalIncome
     */
    protected $AdditionalIncome = null;

    /**
     * @var string $SouceOfAdditionalIncome
     */
    protected $SouceOfAdditionalIncome = null;

    /**
     * @var float $SpouseGrossSalary
     */
    protected $SpouseGrossSalary = null;

    /**
     * @var float $BondRentPayment
     */
    protected $BondRentPayment = null;

    /**
     * @var float $VehicleInstallments
     */
    protected $VehicleInstallments = null;

    /**
     * @var float $CreditCardRepayments
     */
    protected $CreditCardRepayments = null;

    /**
     * @var float $ClothingAccounts
     */
    protected $ClothingAccounts = null;

    /**
     * @var float $PolicyRepayments
     */
    protected $PolicyRepayments = null;

    /**
     * @var float $TransportCost
     */
    protected $TransportCost = null;

    /**
     * @var float $EducationCost
     */
    protected $EducationCost = null;

    /**
     * @var float $HouseholdExpenses
     */
    protected $HouseholdExpenses = null;

    /**
     * @var float $WaterElectricityExpenses
     */
    protected $WaterElectricityExpenses = null;

    /**
     * @var float $PersonalLoanRepayment
     */
    protected $PersonalLoanRepayment = null;

    /**
     * @var float $FurnitureAccounts
     */
    protected $FurnitureAccounts = null;

    /**
     * @var float $OverDraftRepayments
     */
    protected $OverDraftRepayments = null;

    /**
     * @var float $TelephonePayments
     */
    protected $TelephonePayments = null;

    /**
     * @var float $FoodAndEntertainment
     */
    protected $FoodAndEntertainment = null;

    /**
     * @var float $MaintenancExpenses
     */
    protected $MaintenancExpenses = null;

    /**
     * @var float $OtherExpenses
     */
    protected $OtherExpenses = null;

    /**
     * @var float $RentAmount
     */
    protected $RentAmount = null;

    /**
     * @var float $MedicalAid
     */
    protected $MedicalAid = null;

    /**
     * @var float $Commission
     */
    protected $Commission = null;

    /**
     * @param float $monthlyGrossSalary
     * @param float $carAllowance
     * @param float $takeHomeSalary
     * @param float $additionalIncome
     * @param float $spouseGrossSalary
     * @param float $bondRentPayment
     * @param float $vehicleInstallments
     * @param float $creditCardRepayments
     * @param float $clothingAccounts
     * @param float $policyRepayments
     * @param float $transportCost
     * @param float $educationCost
     * @param float $householdExpenses
     * @param float $waterElectricityExpenses
     * @param float $personalLoanRepayment
     * @param float $furnitureAccounts
     * @param float $overDraftRepayments
     * @param float $telephonePayments
     * @param float $foodAndEntertainment
     * @param float $maintenancExpenses
     * @param float $otherExpenses
     * @param float $rentAmount
     * @param float $medicalAid
     * @param float $commission
     */
    public function __construct(
        $monthlyGrossSalary,
        $carAllowance,
        $takeHomeSalary,
        $additionalIncome,
        $spouseGrossSalary,
        $bondRentPayment,
        $vehicleInstallments,
        $creditCardRepayments,
        $clothingAccounts,
        $policyRepayments,
        $transportCost,
        $educationCost,
        $householdExpenses,
        $waterElectricityExpenses,
        $personalLoanRepayment,
        $furnitureAccounts,
        $overDraftRepayments,
        $telephonePayments,
        $foodAndEntertainment,
        $maintenancExpenses,
        $otherExpenses,
        $rentAmount,
        $medicalAid,
        $commission
    ) {
        $this->MonthlyGrossSalary = $monthlyGrossSalary;
        $this->CarAllowance = $carAllowance;
        $this->TakeHomeSalary = $takeHomeSalary;
        $this->AdditionalIncome = $additionalIncome;
        $this->SpouseGrossSalary = $spouseGrossSalary;
        $this->BondRentPayment = $bondRentPayment;
        $this->VehicleInstallments = $vehicleInstallments;
        $this->CreditCardRepayments = $creditCardRepayments;
        $this->ClothingAccounts = $clothingAccounts;
        $this->PolicyRepayments = $policyRepayments;
        $this->TransportCost = $transportCost;
        $this->EducationCost = $educationCost;
        $this->HouseholdExpenses = $householdExpenses;
        $this->WaterElectricityExpenses = $waterElectricityExpenses;
        $this->PersonalLoanRepayment = $personalLoanRepayment;
        $this->FurnitureAccounts = $furnitureAccounts;
        $this->OverDraftRepayments = $overDraftRepayments;
        $this->TelephonePayments = $telephonePayments;
        $this->FoodAndEntertainment = $foodAndEntertainment;
        $this->MaintenancExpenses = $maintenancExpenses;
        $this->OtherExpenses = $otherExpenses;
        $this->RentAmount = $rentAmount;
        $this->MedicalAid = $medicalAid;
        $this->Commission = $commission;
    }

    /**
     * @return float
     */
    public function getMonthlyGrossSalary()
    {
        return $this->MonthlyGrossSalary;
    }

    /**
     * @param float $monthlyGrossSalary
     * @return Peppermint_Dfe_Soap_Application_IncomeDetails
     */
    public function setMonthlyGrossSalary($monthlyGrossSalary)
    {
        $this->MonthlyGrossSalary = $monthlyGrossSalary;

        return $this;
    }

    /**
     * @return float
     */
    public function getCarAllowance()
    {
        return $this->CarAllowance;
    }

    /**
     * @param float $carAllowance
     * @return Peppermint_Dfe_Soap_Application_IncomeDetails
     */
    public function setCarAllowance($carAllowance)
    {
        $this->CarAllowance = $carAllowance;

        return $this;
    }

    /**
     * @return float
     */
    public function getTakeHomeSalary()
    {
        return $this->TakeHomeSalary;
    }

    /**
     * @param float $takeHomeSalary
     * @return Peppermint_Dfe_Soap_Application_IncomeDetails
     */
    public function setTakeHomeSalary($takeHomeSalary)
    {
        $this->TakeHomeSalary = $takeHomeSalary;

        return $this;
    }

    /**
     * @return float
     */
    public function getAdditionalIncome()
    {
        return $this->AdditionalIncome;
    }

    /**
     * @param float $additionalIncome
     * @return Peppermint_Dfe_Soap_Application_IncomeDetails
     */
    public function setAdditionalIncome($additionalIncome)
    {
        $this->AdditionalIncome = $additionalIncome;

        return $this;
    }

    /**
     * @return string
     */
    public function getSouceOfAdditionalIncome()
    {
        return $this->SouceOfAdditionalIncome;
    }

    /**
     * @param string $souceOfAdditionalIncome
     * @return Peppermint_Dfe_Soap_Application_IncomeDetails
     */
    public function setSouceOfAdditionalIncome($souceOfAdditionalIncome)
    {
        $this->SouceOfAdditionalIncome = $souceOfAdditionalIncome;

        return $this;
    }

    /**
     * @return float
     */
    public function getSpouseGrossSalary()
    {
        return $this->SpouseGrossSalary;
    }

    /**
     * @param float $spouseGrossSalary
     * @return Peppermint_Dfe_Soap_Application_IncomeDetails
     */
    public function setSpouseGrossSalary($spouseGrossSalary)
    {
        $this->SpouseGrossSalary = $spouseGrossSalary;

        return $this;
    }

    /**
     * @return float
     */
    public function getBondRentPayment()
    {
        return $this->BondRentPayment;
    }

    /**
     * @param float $bondRentPayment
     * @return Peppermint_Dfe_Soap_Application_IncomeDetails
     */
    public function setBondRentPayment($bondRentPayment)
    {
        $this->BondRentPayment = $bondRentPayment;

        return $this;
    }

    /**
     * @return float
     */
    public function getVehicleInstallments()
    {
        return $this->VehicleInstallments;
    }

    /**
     * @param float $vehicleInstallments
     * @return Peppermint_Dfe_Soap_Application_IncomeDetails
     */
    public function setVehicleInstallments($vehicleInstallments)
    {
        $this->VehicleInstallments = $vehicleInstallments;

        return $this;
    }

    /**
     * @return float
     */
    public function getCreditCardRepayments()
    {
        return $this->CreditCardRepayments;
    }

    /**
     * @param float $creditCardRepayments
     * @return Peppermint_Dfe_Soap_Application_IncomeDetails
     */
    public function setCreditCardRepayments($creditCardRepayments)
    {
        $this->CreditCardRepayments = $creditCardRepayments;

        return $this;
    }

    /**
     * @return float
     */
    public function getClothingAccounts()
    {
        return $this->ClothingAccounts;
    }

    /**
     * @param float $clothingAccounts
     * @return Peppermint_Dfe_Soap_Application_IncomeDetails
     */
    public function setClothingAccounts($clothingAccounts)
    {
        $this->ClothingAccounts = $clothingAccounts;

        return $this;
    }

    /**
     * @return float
     */
    public function getPolicyRepayments()
    {
        return $this->PolicyRepayments;
    }

    /**
     * @param float $policyRepayments
     * @return Peppermint_Dfe_Soap_Application_IncomeDetails
     */
    public function setPolicyRepayments($policyRepayments)
    {
        $this->PolicyRepayments = $policyRepayments;

        return $this;
    }

    /**
     * @return float
     */
    public function getTransportCost()
    {
        return $this->TransportCost;
    }

    /**
     * @param float $transportCost
     * @return Peppermint_Dfe_Soap_Application_IncomeDetails
     */
    public function setTransportCost($transportCost)
    {
        $this->TransportCost = $transportCost;

        return $this;
    }

    /**
     * @return float
     */
    public function getEducationCost()
    {
        return $this->EducationCost;
    }

    /**
     * @param float $educationCost
     * @return Peppermint_Dfe_Soap_Application_IncomeDetails
     */
    public function setEducationCost($educationCost)
    {
        $this->EducationCost = $educationCost;

        return $this;
    }

    /**
     * @return float
     */
    public function getHouseholdExpenses()
    {
        return $this->HouseholdExpenses;
    }

    /**
     * @param float $householdExpenses
     * @return Peppermint_Dfe_Soap_Application_IncomeDetails
     */
    public function setHouseholdExpenses($householdExpenses)
    {
        $this->HouseholdExpenses = $householdExpenses;

        return $this;
    }

    /**
     * @return float
     */
    public function getWaterElectricityExpenses()
    {
        return $this->WaterElectricityExpenses;
    }

    /**
     * @param float $waterElectricityExpenses
     * @return Peppermint_Dfe_Soap_Application_IncomeDetails
     */
    public function setWaterElectricityExpenses($waterElectricityExpenses)
    {
        $this->WaterElectricityExpenses = $waterElectricityExpenses;

        return $this;
    }

    /**
     * @return float
     */
    public function getPersonalLoanRepayment()
    {
        return $this->PersonalLoanRepayment;
    }

    /**
     * @param float $personalLoanRepayment
     * @return Peppermint_Dfe_Soap_Application_IncomeDetails
     */
    public function setPersonalLoanRepayment($personalLoanRepayment)
    {
        $this->PersonalLoanRepayment = $personalLoanRepayment;

        return $this;
    }

    /**
     * @return float
     */
    public function getFurnitureAccounts()
    {
        return $this->FurnitureAccounts;
    }

    /**
     * @param float $furnitureAccounts
     * @return Peppermint_Dfe_Soap_Application_IncomeDetails
     */
    public function setFurnitureAccounts($furnitureAccounts)
    {
        $this->FurnitureAccounts = $furnitureAccounts;

        return $this;
    }

    /**
     * @return float
     */
    public function getOverDraftRepayments()
    {
        return $this->OverDraftRepayments;
    }

    /**
     * @param float $overDraftRepayments
     * @return Peppermint_Dfe_Soap_Application_IncomeDetails
     */
    public function setOverDraftRepayments($overDraftRepayments)
    {
        $this->OverDraftRepayments = $overDraftRepayments;

        return $this;
    }

    /**
     * @return float
     */
    public function getTelephonePayments()
    {
        return $this->TelephonePayments;
    }

    /**
     * @param float $telephonePayments
     * @return Peppermint_Dfe_Soap_Application_IncomeDetails
     */
    public function setTelephonePayments($telephonePayments)
    {
        $this->TelephonePayments = $telephonePayments;

        return $this;
    }

    /**
     * @return float
     */
    public function getFoodAndEntertainment()
    {
        return $this->FoodAndEntertainment;
    }

    /**
     * @param float $foodAndEntertainment
     * @return Peppermint_Dfe_Soap_Application_IncomeDetails
     */
    public function setFoodAndEntertainment($foodAndEntertainment)
    {
        $this->FoodAndEntertainment = $foodAndEntertainment;

        return $this;
    }

    /**
     * @return float
     */
    public function getMaintenancExpenses()
    {
        return $this->MaintenancExpenses;
    }

    /**
     * @param float $maintenancExpenses
     * @return Peppermint_Dfe_Soap_Application_IncomeDetails
     */
    public function setMaintenancExpenses($maintenancExpenses)
    {
        $this->MaintenancExpenses = $maintenancExpenses;

        return $this;
    }

    /**
     * @return float
     */
    public function getOtherExpenses()
    {
        return $this->OtherExpenses;
    }

    /**
     * @param float $otherExpenses
     * @return Peppermint_Dfe_Soap_Application_IncomeDetails
     */
    public function setOtherExpenses($otherExpenses)
    {
        $this->OtherExpenses = $otherExpenses;

        return $this;
    }

    /**
     * @return float
     */
    public function getRentAmount()
    {
        return $this->RentAmount;
    }

    /**
     * @param float $rentAmount
     * @return Peppermint_Dfe_Soap_Application_IncomeDetails
     */
    public function setRentAmount($rentAmount)
    {
        $this->RentAmount = $rentAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getMedicalAid()
    {
        return $this->MedicalAid;
    }

    /**
     * @param float $medicalAid
     * @return Peppermint_Dfe_Soap_Application_IncomeDetails
     */
    public function setMedicalAid($medicalAid)
    {
        $this->MedicalAid = $medicalAid;

        return $this;
    }

    /**
     * @return float
     */
    public function getCommission()
    {
        return $this->Commission;
    }

    /**
     * @param float $commission
     * @return Peppermint_Dfe_Soap_Application_IncomeDetails
     */
    public function setCommission($commission)
    {
        $this->Commission = $commission;

        return $this;
    }
}

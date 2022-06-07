<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_Application_CustomerDetails
{
    /**
     * @var string $CompanyName
     */
    protected $CompanyName = null;

    /**
     * @var string $CompanyType
     */
    protected $CompanyType = null;

    /**
     * @var string $BusinessCategory
     */
    protected $BusinessCategory = null;

    /**
     * @var string $FinanceCategory
     */
    protected $FinanceCategory = null;

    /**
     * @var string $RegistrationNumber
     */
    protected $RegistrationNumber = null;

    /**
     * @var string $VatNumber
     */
    protected $VatNumber = null;

    /**
     * @var string $RepresentativeContactName
     */
    protected $RepresentativeContactName = null;

    /**
     * @var string $RepresentativeDesignation
     */
    protected $RepresentativeDesignation = null;

    /**
     * @var string $RepresentativeCellularNumber
     */
    protected $RepresentativeCellularNumber = null;

    /**
     * @var string $Telephone
     */
    protected $Telephone = null;

    /**
     * @var string $YearEndDate
     */
    protected $YearEndDate = null;

    /**
     * @var string $Premises
     */
    protected $Premises = null;

    /**
     * @var string $RegisteredOwner
     */
    protected $RegisteredOwner = null;

    /**
     * @var string $HoldingCompanyName
     */
    protected $HoldingCompanyName = null;

    /**
     * @var string $HoldingCompanyRegNo
     */
    protected $HoldingCompanyRegNo = null;

    /**
     * @var string $RepresentativeEmailAddress
     */
    protected $RepresentativeEmailAddress = null;

    /**
     * @var boolean $MO_EXCLUSIVE_DEALS_BMW
     */
    protected $MO_EXCLUSIVE_DEALS_BMW = null;

    /**
     * @var boolean $MO_SHARE_DETAILS_BMW
     */
    protected $MO_SHARE_DETAILS_BMW = null;

    /**
     * @var boolean $MO_TELEMARKETING_BMW
     */
    protected $MO_TELEMARKETING_BMW = null;

    /**
     * @var boolean $MO_MASS_DISTRIBUTION_BMW
     */
    protected $MO_MASS_DISTRIBUTION_BMW = null;

    /**
     * @var Peppermint_Dfe_Soap_Application_AssociatedCompanies[] $AssociatedCompanies
     */
    protected $AssociatedCompanies = null;

    /**
     * @var Peppermint_Dfe_Soap_Application_PersonnelInformation[] $PersonalInformation
     */
    protected $PersonalInformation = null;

    /**
     * @return string
     */
    public function getCompanyName()
    {
        return $this->CompanyName;
    }

    /**
     * @param string $companyName
     * @return Peppermint_Dfe_Soap_Application_CustomerDetails
     */
    public function setCompanyName($companyName)
    {
        $this->CompanyName = $companyName;

        return $this;
    }

    /**
     * @return string
     */
    public function getCompanyType()
    {
        return $this->CompanyType;
    }

    /**
     * @param string $companyType
     * @return Peppermint_Dfe_Soap_Application_CustomerDetails
     */
    public function setCompanyType($companyType)
    {
        $this->CompanyType = $companyType;

        return $this;
    }

    /**
     * @return string
     */
    public function getBusinessCategory()
    {
        return $this->BusinessCategory;
    }

    /**
     * @param string $businessCategory
     * @return Peppermint_Dfe_Soap_Application_CustomerDetails
     */
    public function setBusinessCategory($businessCategory)
    {
        $this->BusinessCategory = $businessCategory;

        return $this;
    }

    /**
     * @return string
     */
    public function getFinanceCategory()
    {
        return $this->FinanceCategory;
    }

    /**
     * @param string $financeCategory
     * @return Peppermint_Dfe_Soap_Application_CustomerDetails
     */
    public function setFinanceCategory($financeCategory)
    {
        $this->FinanceCategory = $financeCategory;

        return $this;
    }

    /**
     * @return string
     */
    public function getRegistrationNumber()
    {
        return $this->RegistrationNumber;
    }

    /**
     * @param string $registrationNumber
     * @return Peppermint_Dfe_Soap_Application_CustomerDetails
     */
    public function setRegistrationNumber($registrationNumber)
    {
        $this->RegistrationNumber = $registrationNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getVatNumber()
    {
        return $this->VatNumber;
    }

    /**
     * @param string $vatNumber
     * @return Peppermint_Dfe_Soap_Application_CustomerDetails
     */
    public function setVatNumber($vatNumber)
    {
        $this->VatNumber = $vatNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getRepresentativeContactName()
    {
        return $this->RepresentativeContactName;
    }

    /**
     * @param string $representativeContactName
     * @return Peppermint_Dfe_Soap_Application_CustomerDetails
     */
    public function setRepresentativeContactName($representativeContactName)
    {
        $this->RepresentativeContactName = $representativeContactName;

        return $this;
    }

    /**
     * @return string
     */
    public function getRepresentativeDesignation()
    {
        return $this->RepresentativeDesignation;
    }

    /**
     * @param string $representativeDesignation
     * @return Peppermint_Dfe_Soap_Application_CustomerDetails
     */
    public function setRepresentativeDesignation($representativeDesignation)
    {
        $this->RepresentativeDesignation = $representativeDesignation;

        return $this;
    }

    /**
     * @return string
     */
    public function getRepresentativeCellularNumber()
    {
        return $this->RepresentativeCellularNumber;
    }

    /**
     * @param string $representativeCellularNumber
     * @return Peppermint_Dfe_Soap_Application_CustomerDetails
     */
    public function setRepresentativeCellularNumber($representativeCellularNumber)
    {
        $this->RepresentativeCellularNumber = $representativeCellularNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getTelephone()
    {
        return $this->Telephone;
    }

    /**
     * @param string $telephone
     * @return Peppermint_Dfe_Soap_Application_CustomerDetails
     */
    public function setTelephone($telephone)
    {
        $this->Telephone = $telephone;

        return $this;
    }

    /**
     * @return string
     */
    public function getYearEndDate()
    {
        return $this->YearEndDate;
    }

    /**
     * @param string $yearEndDate
     * @return Peppermint_Dfe_Soap_Application_CustomerDetails
     */
    public function setYearEndDate($yearEndDate)
    {
        $this->YearEndDate = $yearEndDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getPremises()
    {
        return $this->Premises;
    }

    /**
     * @param string $premises
     * @return Peppermint_Dfe_Soap_Application_CustomerDetails
     */
    public function setPremises($premises)
    {
        $this->Premises = $premises;

        return $this;
    }

    /**
     * @return string
     */
    public function getRegisteredOwner()
    {
        return $this->RegisteredOwner;
    }

    /**
     * @param string $registeredOwner
     * @return Peppermint_Dfe_Soap_Application_CustomerDetails
     */
    public function setRegisteredOwner($registeredOwner)
    {
        $this->RegisteredOwner = $registeredOwner;

        return $this;
    }

    /**
     * @return string
     */
    public function getHoldingCompanyName()
    {
        return $this->HoldingCompanyName;
    }

    /**
     * @param string $holdingCompanyName
     * @return Peppermint_Dfe_Soap_Application_CustomerDetails
     */
    public function setHoldingCompanyName($holdingCompanyName)
    {
        $this->HoldingCompanyName = $holdingCompanyName;

        return $this;
    }

    /**
     * @return string
     */
    public function getHoldingCompanyRegNo()
    {
        return $this->HoldingCompanyRegNo;
    }

    /**
     * @param string $holdingCompanyRegNo
     * @return Peppermint_Dfe_Soap_Application_CustomerDetails
     */
    public function setHoldingCompanyRegNo($holdingCompanyRegNo)
    {
        $this->HoldingCompanyRegNo = $holdingCompanyRegNo;

        return $this;
    }

    /**
     * @return string
     */
    public function getRepresentativeEmailAddress()
    {
        return $this->RepresentativeEmailAddress;
    }

    /**
     * @param string $representativeEmailAddress
     * @return Peppermint_Dfe_Soap_Application_CustomerDetails
     */
    public function setRepresentativeEmailAddress($representativeEmailAddress)
    {
        $this->RepresentativeEmailAddress = $representativeEmailAddress;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getMoExclusiveDealsBMW()
    {
        return $this->MO_EXCLUSIVE_DEALS_BMW;
    }

    /**
     * @param boolean $moExclusiveDealsBMW
     * @return Peppermint_Dfe_Soap_Application_CustomerDetails
     */
    public function setMoExclusiveDealsBMW($moExclusiveDealsBMW)
    {
        $this->MO_EXCLUSIVE_DEALS_BMW = $moExclusiveDealsBMW;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getMoShareDetailsBMW()
    {
        return $this->MO_SHARE_DETAILS_BMW;
    }

    /**
     * @param boolean $moShareDetailsBMW
     * @return Peppermint_Dfe_Soap_Application_CustomerDetails
     */
    public function setMoShareDetailsBMW($moShareDetailsBMW)
    {
        $this->MO_SHARE_DETAILS_BMW = $moShareDetailsBMW;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getMoTelemarketingBMW()
    {
        return $this->MO_TELEMARKETING_BMW;
    }

    /**
     * @param boolean $moTelemarketingBMW
     * @return Peppermint_Dfe_Soap_Application_CustomerDetails
     */
    public function setMoTelemarketingBMW($moTelemarketingBMW)
    {
        $this->MO_TELEMARKETING_BMW = $moTelemarketingBMW;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getMoMassDistributionBMW()
    {
        return $this->MO_MASS_DISTRIBUTION_BMW;
    }

    /**
     * @param boolean $moMassDistributionBMW
     * @return Peppermint_Dfe_Soap_Application_CustomerDetails
     */
    public function setMoMassDistributionBMW($moMassDistributionBMW)
    {
        $this->MO_MASS_DISTRIBUTION_BMW = $moMassDistributionBMW;

        return $this;
    }

    /**
     * @return Peppermint_Dfe_Soap_Application_AssociatedCompanies[]
     */
    public function getAssociatedCompanies()
    {
        return $this->AssociatedCompanies;
    }

    /**
     * @param Peppermint_Dfe_Soap_Application_AssociatedCompanies[] $associatedCompanies
     * @return Peppermint_Dfe_Soap_Application_CustomerDetails
     */
    public function setAssociatedCompanies(array $associatedCompanies = null)
    {
        $this->AssociatedCompanies = $associatedCompanies;

        return $this;
    }

    /**
     * @return Peppermint_Dfe_Soap_Application_PersonnelInformation[]
     */
    public function getPersonalInformation()
    {
        return $this->PersonalInformation;
    }

    /**
     * @param Peppermint_Dfe_Soap_Application_PersonnelInformation[] $personalInformation
     * @return Peppermint_Dfe_Soap_Application_CustomerDetails
     */
    public function setPersonalInformation(array $personalInformation = null)
    {
        $this->PersonalInformation = $personalInformation;

        return $this;
    }
}

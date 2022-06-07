<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_Application_IndividualCustomerDetails
{
    /**
     * @var Peppermint_Dfe_Soap_Application_Applicant $Applicant
     */
    protected $Applicant = null;

    /**
     * @var Peppermint_Dfe_Soap_Application_Address $ResidentialAddress
     */
    protected $ResidentialAddress = null;

    /**
     * @var Peppermint_Dfe_Soap_Application_Address $NextOfKinAddress
     */
    protected $NextOfKinAddress = null;

    /**
     * @var Peppermint_Dfe_Soap_Application_Address $EmployerAddress
     */
    protected $EmployerAddress = null;

    /**
     * @var Peppermint_Dfe_Soap_Application_Address $PostalAddress
     */
    protected $PostalAddress = null;

    /**
     * @var Peppermint_Dfe_Soap_Application_Address $PreviousAddress
     */
    protected $PreviousAddress = null;

    /**
     * @var Peppermint_Dfe_Soap_Application_BasicDetails $BasicDetails
     */
    protected $BasicDetails = null;

    /**
     * @var Peppermint_Dfe_Soap_Application_EmploymentDetails $EmploymentDetails
     */
    protected $EmploymentDetails = null;

    /**
     * @var Peppermint_Dfe_Soap_Application_IncomeDetails $IncomeDetails
     */
    protected $IncomeDetails = null;

    /**
     * @var Peppermint_Dfe_Soap_Application_ResidentialInformationDetails $ResidentialInformationDetails
     */
    protected $ResidentialInformationDetails = null;

    /**
     * @var Peppermint_Dfe_Soap_Application_BankDetails $BankDetails
     */
    protected $BankDetails = null;

    /**
     * @var Peppermint_Dfe_Soap_Application_ContactDetails $ContactDetails
     */
    protected $ContactDetails = null;

    /**
     * @return Peppermint_Dfe_Soap_Application_Applicant
     */
    public function getApplicant()
    {
        return $this->Applicant;
    }

    /**
     * @param Peppermint_Dfe_Soap_Application_Applicant $applicant
     * @return Peppermint_Dfe_Soap_Application_IndividualCustomerDetails
     */
    public function setApplicant($applicant)
    {
        $this->Applicant = $applicant;

        return $this;
    }

    /**
     * @return Peppermint_Dfe_Soap_Application_Address
     */
    public function getResidentialAddress()
    {
        return $this->ResidentialAddress;
    }

    /**
     * @param Peppermint_Dfe_Soap_Application_Address $residentialAddress
     * @return Peppermint_Dfe_Soap_Application_IndividualCustomerDetails
     */
    public function setResidentialAddress($residentialAddress)
    {
        $this->ResidentialAddress = $residentialAddress;

        return $this;
    }

    /**
     * @return Peppermint_Dfe_Soap_Application_Address
     */
    public function getNextOfKinAddress()
    {
        return $this->NextOfKinAddress;
    }

    /**
     * @param Peppermint_Dfe_Soap_Application_Address $nextOfKinAddress
     * @return Peppermint_Dfe_Soap_Application_IndividualCustomerDetails
     */
    public function setNextOfKinAddress($nextOfKinAddress)
    {
        $this->NextOfKinAddress = $nextOfKinAddress;

        return $this;
    }

    /**
     * @return Peppermint_Dfe_Soap_Application_Address
     */
    public function getEmployerAddress()
    {
        return $this->EmployerAddress;
    }

    /**
     * @param Peppermint_Dfe_Soap_Application_Address $employerAddress
     * @return Peppermint_Dfe_Soap_Application_IndividualCustomerDetails
     */
    public function setEmployerAddress($employerAddress)
    {
        $this->EmployerAddress = $employerAddress;

        return $this;
    }

    /**
     * @return Peppermint_Dfe_Soap_Application_Address
     */
    public function getPostalAddress()
    {
        return $this->PostalAddress;
    }

    /**
     * @param Peppermint_Dfe_Soap_Application_Address $postalAddress
     * @return Peppermint_Dfe_Soap_Application_IndividualCustomerDetails
     */
    public function setPostalAddress($postalAddress)
    {
        $this->PostalAddress = $postalAddress;

        return $this;
    }

    /**
     * @return Peppermint_Dfe_Soap_Application_Address
     */
    public function getPreviousAddress()
    {
        return $this->PreviousAddress;
    }

    /**
     * @param Peppermint_Dfe_Soap_Application_Address $previousAddress
     * @return Peppermint_Dfe_Soap_Application_IndividualCustomerDetails
     */
    public function setPreviousAddress($previousAddress)
    {
        $this->PreviousAddress = $previousAddress;

        return $this;
    }

    /**
     * @return Peppermint_Dfe_Soap_Application_BasicDetails
     */
    public function getBasicDetails()
    {
        return $this->BasicDetails;
    }

    /**
     * @param Peppermint_Dfe_Soap_Application_BasicDetails $basicDetails
     * @return Peppermint_Dfe_Soap_Application_IndividualCustomerDetails
     */
    public function setBasicDetails($basicDetails)
    {
        $this->BasicDetails = $basicDetails;

        return $this;
    }

    /**
     * @return Peppermint_Dfe_Soap_Application_EmploymentDetails
     */
    public function getEmploymentDetails()
    {
        return $this->EmploymentDetails;
    }

    /**
     * @param Peppermint_Dfe_Soap_Application_EmploymentDetails $employmentDetails
     * @return Peppermint_Dfe_Soap_Application_IndividualCustomerDetails
     */
    public function setEmploymentDetails($employmentDetails)
    {
        $this->EmploymentDetails = $employmentDetails;

        return $this;
    }

    /**
     * @return Peppermint_Dfe_Soap_Application_IncomeDetails
     */
    public function getIncomeDetails()
    {
        return $this->IncomeDetails;
    }

    /**
     * @param Peppermint_Dfe_Soap_Application_IncomeDetails $incomeDetails
     * @return Peppermint_Dfe_Soap_Application_IndividualCustomerDetails
     */
    public function setIncomeDetails($incomeDetails)
    {
        $this->IncomeDetails = $incomeDetails;

        return $this;
    }

    /**
     * @return Peppermint_Dfe_Soap_Application_ResidentialInformationDetails
     */
    public function getResidentialInformationDetails()
    {
        return $this->ResidentialInformationDetails;
    }

    /**
     * @param Peppermint_Dfe_Soap_Application_ResidentialInformationDetails $residentialInformationDetails
     * @return Peppermint_Dfe_Soap_Application_IndividualCustomerDetails
     */
    public function setResidentialInformationDetails($residentialInformationDetails)
    {
        $this->ResidentialInformationDetails = $residentialInformationDetails;

        return $this;
    }

    /**
     * @return Peppermint_Dfe_Soap_Application_BankDetails
     */
    public function getBankDetails()
    {
        return $this->BankDetails;
    }

    /**
     * @param Peppermint_Dfe_Soap_Application_BankDetails $bankDetails
     * @return Peppermint_Dfe_Soap_Application_IndividualCustomerDetails
     */
    public function setBankDetails($bankDetails)
    {
        $this->BankDetails = $bankDetails;

        return $this;
    }

    /**
     * @return Peppermint_Dfe_Soap_Application_ContactDetails
     */
    public function getContactDetails()
    {
        return $this->ContactDetails;
    }

    /**
     * @param Peppermint_Dfe_Soap_Application_ContactDetails $contactDetails
     * @return Peppermint_Dfe_Soap_Application_IndividualCustomerDetails
     */
    public function setContactDetails($contactDetails)
    {
        $this->ContactDetails = $contactDetails;

        return $this;
    }
}

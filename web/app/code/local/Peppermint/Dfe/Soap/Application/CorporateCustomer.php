<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_Application_CorporateCustomer
{
    /**
     * @var Peppermint_Dfe_Soap_Application_Applicant $Applicant
     */
    protected $Applicant = null;

    /**
     * @var Peppermint_Dfe_Soap_Application_Address $BusinessAddress
     */
    protected $BusinessAddress = null;

    /**
     * @var Peppermint_Dfe_Soap_Application_Address $PostalAddress
     */
    protected $PostalAddress = null;

    /**
     * @var Peppermint_Dfe_Soap_Application_CustomerDetails $CustomerDetails
     */
    protected $CustomerDetails = null;

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
     * @param Applicant $applicant
     * @return Peppermint_Dfe_Soap_Application_CorporateCustomer
     */
    public function setApplicant($applicant)
    {
        $this->Applicant = $applicant;

        return $this;
    }

    /**
     * @return Peppermint_Dfe_Soap_Application_Address
     */
    public function getBusinessAddress()
    {
        return $this->BusinessAddress;
    }

    /**
     * @param Peppermint_Dfe_Soap_Application_Address $businessAddress
     * @return Peppermint_Dfe_Soap_Application_CorporateCustomer
     */
    public function setBusinessAddress($businessAddress)
    {
        $this->BusinessAddress = $businessAddress;

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
     * @return Peppermint_Dfe_Soap_Application_CorporateCustomer
     */
    public function setPostalAddress($postalAddress)
    {
        $this->PostalAddress = $postalAddress;

        return $this;
    }

    /**
     * @return Peppermint_Dfe_Soap_Application_CustomerDetails
     */
    public function getCustomerDetails()
    {
        return $this->CustomerDetails;
    }

    /**
     * @param Peppermint_Dfe_Soap_Application_CustomerDetails $customerDetails
     * @return Peppermint_Dfe_Soap_Application_CorporateCustomer
     */
    public function setCustomerDetails($customerDetails)
    {
        $this->CustomerDetails = $customerDetails;

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
     * @return Peppermint_Dfe_Soap_Application_CorporateCustomer
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
     * @return Peppermint_Dfe_Soap_Application_CorporateCustomer
     */
    public function setContactDetails($contactDetails)
    {
        $this->ContactDetails = $contactDetails;

        return $this;
    }
}

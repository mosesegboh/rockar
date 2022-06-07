<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_Application_AssociatedCompanies
{
    /**
     * @var string $AssociatedCompany
     */
    protected $AssociatedCompany = null;

    /**
     * @var string $RegistrationNumber
     */
    protected $RegistrationNumber = null;

    /**
     * @return string
     */
    public function getAssociatedCompany()
    {
        return $this->AssociatedCompany;
    }

    /**
     * @param string $associatedCompany
     * @return Peppermint_Dfe_Soap_Application_AssociatedCompanies
     */
    public function setAssociatedCompany($associatedCompany)
    {
        $this->AssociatedCompany = $associatedCompany;

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
     * @return Peppermint_Dfe_Soap_Application_AssociatedCompanies
     */
    public function setRegistrationNumber($registrationNumber)
    {
        $this->RegistrationNumber = $registrationNumber;

        return $this;
    }
}

<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_Application_PersonnelInformation
{
    /**
     * @var string $NameOfShareHolder
     */
    protected $NameOfShareHolder = null;

    /**
     * @var string $TelephoneNumber
     */
    protected $TelephoneNumber = null;

    /**
     * @var string $IDNumber
     */
    protected $IDNumber = null;

    /**
     * @return string
     */
    public function getNameOfShareHolder()
    {
        return $this->NameOfShareHolder;
    }

    /**
     * @param string $nameOfShareHolder
     * @return Peppermint_Dfe_Soap_Application_PersonnelInformation
     */
    public function setNameOfShareHolder($nameOfShareHolder)
    {
        $this->NameOfShareHolder = $nameOfShareHolder;

        return $this;
    }

    /**
     * @return string
     */
    public function getTelephoneNumber()
    {
        return $this->TelephoneNumber;
    }

    /**
     * @param string $telephoneNumber
     * @return Peppermint_Dfe_Soap_Application_PersonnelInformation
     */
    public function setTelephoneNumber($telephoneNumber)
    {
        $this->TelephoneNumber = $telephoneNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getIDNumber()
    {
        return $this->IDNumber;
    }

    /**
     * @param string $iDNumber
     * @return Peppermint_Dfe_Soap_Application_PersonnelInformation
     */
    public function setIDNumber($iDNumber)
    {
        $this->IDNumber = $iDNumber;

        return $this;
    }
}

<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_Application_ResidentialInformationDetails
{
    /**
     * @var string $TypeOfHouse
     */
    protected $TypeOfHouse = null;

    /**
     * @var string $ResidentialStatusCode
     */
    protected $ResidentialStatusCode = null;

    /**
     * @var string $ResidentialOwner
     */
    protected $ResidentialOwner = null;

    /**
     * @var string $HomeStatusCode
     */
    protected $HomeStatusCode = null;

    /**
     * @var float $BalanceOwing
     */
    protected $BalanceOwing = null;

    /**
     * @var string $AccessFacilityFlag
     */
    protected $AccessFacilityFlag = null;

    /**
     * @var float $FaciltyValue
     */
    protected $FaciltyValue = null;

    /**
     * @return string
     */
    public function getTypeOfHouse()
    {
        return $this->TypeOfHouse;
    }

    /**
     * @param string $typeOfHouse
     * @return Peppermint_Dfe_Soap_Application_ResidentialInformationDetails
     */
    public function setTypeOfHouse($typeOfHouse)
    {
        $this->TypeOfHouse = $typeOfHouse;

        return $this;
    }

    /**
     * @return string
     */
    public function getResidentialStatusCode()
    {
        return $this->ResidentialStatusCode;
    }

    /**
     * @param string $residentialStatusCode
     * @return Peppermint_Dfe_Soap_Application_ResidentialInformationDetails
     */
    public function setResidentialStatusCode($residentialStatusCode)
    {
        $this->ResidentialStatusCode = $residentialStatusCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getResidentialOwner()
    {
        return $this->ResidentialOwner;
    }

    /**
     * @param string $residentialOwner
     * @return Peppermint_Dfe_Soap_Application_ResidentialInformationDetails
     */
    public function setResidentialOwner($residentialOwner)
    {
        $this->ResidentialOwner = $residentialOwner;

        return $this;
    }

    /**
     * @return string
     */
    public function getHomeStatusCode()
    {
        return $this->HomeStatusCode;
    }

    /**
     * @param string $homeStatusCode
     * @return Peppermint_Dfe_Soap_Application_ResidentialInformationDetails
     */
    public function setHomeStatusCode($homeStatusCode)
    {
        $this->HomeStatusCode = $homeStatusCode;

        return $this;
    }

    /**
     * @return float
     */
    public function getBalanceOwing()
    {
        return $this->BalanceOwing;
    }

    /**
     * @param float $balanceOwing
     * @return Peppermint_Dfe_Soap_Application_ResidentialInformationDetails
     */
    public function setBalanceOwing($balanceOwing)
    {
        $this->BalanceOwing = $balanceOwing;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccessFacilityFlag()
    {
        return $this->AccessFacilityFlag;
    }

    /**
     * @param string $accessFacilityFlag
     * @return Peppermint_Dfe_Soap_Application_ResidentialInformationDetails
     */
    public function setAccessFacilityFlag($accessFacilityFlag)
    {
        $this->AccessFacilityFlag = $accessFacilityFlag;

        return $this;
    }

    /**
     * @return float
     */
    public function getFaciltyValue()
    {
        return $this->FaciltyValue;
    }

    /**
     * @param float $faciltyValue
     * @return Peppermint_Dfe_Soap_Application_ResidentialInformationDetails
     */
    public function setFaciltyValue($faciltyValue)
    {
        $this->FaciltyValue = $faciltyValue;

        return $this;
    }
}

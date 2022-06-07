<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_Application_Address
{
    /**
     * @var string $AddressLine1
     */
    protected $AddressLine1 = null;

    /**
     * @var string $AddressLine2
     */
    protected $AddressLine2 = null;

    /**
     * @var string $AddressLine3
     */
    protected $AddressLine3 = null;

    /**
     * @var string $Suburb
     */
    protected $Suburb = null;

    /**
     * @var string $PostalCode
     */
    protected $PostalCode = null;

    /**
     * @var int $TimeAtAddressInYears
     */
    protected $TimeAtAddressInYears = null;

    /**
     * @var int $TimeAtAddressInMonths
     */
    protected $TimeAtAddressInMonths = null;

    /**
     * @return string
     */
    public function getAddressLine1()
    {
        return $this->AddressLine1;
    }

    /**
     * @param string $addressLine1
     * @return Peppermint_Dfe_Soap_Application_Address
     */
    public function setAddressLine1($addressLine1)
    {
        $this->AddressLine1 = $addressLine1;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddressLine2()
    {
        return $this->AddressLine2;
    }

    /**
     * @param string $addressLine2
     * @return Peppermint_Dfe_Soap_Application_Address
     */
    public function setAddressLine2($addressLine2)
    {
        $this->AddressLine2 = $addressLine2;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddressLine3()
    {
        return $this->AddressLine3;
    }

    /**
     * @param string $addressLine3
     * @return Peppermint_Dfe_Soap_Application_Address
     */
    public function setAddressLine3($addressLine3)
    {
        $this->AddressLine3 = $addressLine3;

        return $this;
    }

    /**
     * @return string
     */
    public function getSuburb()
    {
        return $this->Suburb;
    }

    /**
     * @param string $suburb
     * @return Peppermint_Dfe_Soap_Application_Address
     */
    public function setSuburb($suburb)
    {
        $this->Suburb = $suburb;

        return $this;
    }

    /**
     * @return string
     */
    public function getPostalCode()
    {
        return $this->PostalCode;
    }

    /**
     * @param string $postalCode
     * @return Peppermint_Dfe_Soap_Application_Address
     */
    public function setPostalCode($postalCode)
    {
        $this->PostalCode = $postalCode;

        return $this;
    }

    /**
     * @return int
     */
    public function getTimeAtAddressInYears()
    {
        return $this->TimeAtAddressInYears;
    }

    /**
     * @param int $timeAtAddressInYears
     * @return Peppermint_Dfe_Soap_Application_Address
     */
    public function setTimeAtAddressInYears($timeAtAddressInYears)
    {
        $this->TimeAtAddressInYears = $timeAtAddressInYears;

        return $this;
    }

    /**
     * @return int
     */
    public function getTimeAtAddressInMonths()
    {
        return $this->TimeAtAddressInMonths;
    }

    /**
     * @param int $timeAtAddressInMonths
     * @return Peppermint_Dfe_Soap_Application_Address
     */
    public function setTimeAtAddressInMonths($timeAtAddressInMonths)
    {
        $this->TimeAtAddressInMonths = $timeAtAddressInMonths;

        return $this;
    }
}
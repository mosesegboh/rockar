<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_Application_EmploymentDetails
{
    /**
     * @var string $EmploymentStatusCode
     */
    protected $EmploymentStatusCode = null;

    /**
     * @var string $PresentEmployerName
     */
    protected $PresentEmployerName = null;

    /**
     * @var string $IndustryTypeCode
     */
    protected $IndustryTypeCode = null;

    /**
     * @var int $TimeAtPresentEmployerInYears
     */
    protected $TimeAtPresentEmployerInYears = null;

    /**
     * @var int $TimeAtPresentEmployerInMonths
     */
    protected $TimeAtPresentEmployerInMonths = null;

    /**
     * @var string $Occupation
     */
    protected $Occupation = null;

    /**
     * @var string $PresentEmployerPhoneNumber
     */
    protected $PresentEmployerPhoneNumber = null;

    /**
     * @var string $PreviousEmployerName
     */
    protected $PreviousEmployerName = null;

    /**
     * @var int $TimeAtPreviousEmployerInYears
     */
    protected $TimeAtPreviousEmployerInYears = null;

    /**
     * @var int $TimeAtPreviousEmployerInMonths
     */
    protected $TimeAtPreviousEmployerInMonths = null;

    /**
     * @var string $SpouseEmployerName
     */
    protected $SpouseEmployerName = null;

    /**
     * @var string $SpouseEmploymentStatusCode
     */
    protected $SpouseEmploymentStatusCode = null;

    /**
     * @var int $SpouseEmploymentDurationInYears
     */
    protected $SpouseEmploymentDurationInYears = null;

    /**
     * @var int $SpouseEmploymentDurationInMonths
     */
    protected $SpouseEmploymentDurationInMonths = null;

    /**
     * @param int $timeAtPresentEmployerInYears
     * @param int $timeAtPresentEmployerInMonths
     */
    public function __construct($timeAtPresentEmployerInYears, $timeAtPresentEmployerInMonths)
    {
        $this->TimeAtPresentEmployerInYears = $timeAtPresentEmployerInYears;
        $this->TimeAtPresentEmployerInMonths = $timeAtPresentEmployerInMonths;
    }

    /**
     * @return string
     */
    public function getEmploymentStatusCode()
    {
        return $this->EmploymentStatusCode;
    }

    /**
     * @param string $employmentStatusCode
     * @return Peppermint_Dfe_Soap_Application_EmploymentDetails
     */
    public function setEmploymentStatusCode($employmentStatusCode)
    {
        $this->EmploymentStatusCode = $employmentStatusCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getPresentEmployerName()
    {
        return $this->PresentEmployerName;
    }

    /**
     * @param string $presentEmployerName
     * @return Peppermint_Dfe_Soap_Application_EmploymentDetails
     */
    public function setPresentEmployerName($presentEmployerName)
    {
        $this->PresentEmployerName = $presentEmployerName;

        return $this;
    }

    /**
     * @return string
     */
    public function getIndustryTypeCode()
    {
        return $this->IndustryTypeCode;
    }

    /**
     * @param string $industryTypeCode
     * @return Peppermint_Dfe_Soap_Application_EmploymentDetails
     */
    public function setIndustryTypeCode($industryTypeCode)
    {
        $this->IndustryTypeCode = $industryTypeCode;

        return $this;
    }

    /**
     * @return int
     */
    public function getTimeAtPresentEmployerInYears()
    {
        return $this->TimeAtPresentEmployerInYears;
    }

    /**
     * @param int $timeAtPresentEmployerInYears
     * @return Peppermint_Dfe_Soap_Application_EmploymentDetails
     */
    public function setTimeAtPresentEmployerInYears($timeAtPresentEmployerInYears)
    {
        $this->TimeAtPresentEmployerInYears = $timeAtPresentEmployerInYears;

        return $this;
    }

    /**
     * @return int
     */
    public function getTimeAtPresentEmployerInMonths()
    {
        return $this->TimeAtPresentEmployerInMonths;
    }

    /**
     * @param int $timeAtPresentEmployerInMonths
     * @return Peppermint_Dfe_Soap_Application_EmploymentDetails
     */
    public function setTimeAtPresentEmployerInMonths($timeAtPresentEmployerInMonths)
    {
        $this->TimeAtPresentEmployerInMonths = $timeAtPresentEmployerInMonths;

        return $this;
    }

    /**
     * @return string
     */
    public function getOccupation()
    {
        return $this->Occupation;
    }

    /**
     * @param string $occupation
     * @return Peppermint_Dfe_Soap_Application_EmploymentDetails
     */
    public function setOccupation($occupation)
    {
        $this->Occupation = $occupation;

        return $this;
    }

    /**
     * @return string
     */
    public function getPresentEmployerPhoneNumber()
    {
        return $this->PresentEmployerPhoneNumber;
    }

    /**
     * @param string $presentEmployerPhoneNumber
     * @return Peppermint_Dfe_Soap_Application_EmploymentDetails
     */
    public function setPresentEmployerPhoneNumber($presentEmployerPhoneNumber)
    {
        $this->PresentEmployerPhoneNumber = $presentEmployerPhoneNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getPreviousEmployerName()
    {
        return $this->PreviousEmployerName;
    }

    /**
     * @param string $previousEmployerName
     * @return Peppermint_Dfe_Soap_Application_EmploymentDetails
     */
    public function setPreviousEmployerName($previousEmployerName)
    {
        $this->PreviousEmployerName = $previousEmployerName;

        return $this;
    }

    /**
     * @return int
     */
    public function getTimeAtPreviousEmployerInYears()
    {
        return $this->TimeAtPreviousEmployerInYears;
    }

    /**
     * @param int $timeAtPreviousEmployerInYears
     * @return Peppermint_Dfe_Soap_Application_EmploymentDetails
     */
    public function setTimeAtPreviousEmployerInYears($timeAtPreviousEmployerInYears)
    {
        $this->TimeAtPreviousEmployerInYears = $timeAtPreviousEmployerInYears;

        return $this;
    }

    /**
     * @return int
     */
    public function getTimeAtPreviousEmployerInMonths()
    {
        return $this->TimeAtPreviousEmployerInMonths;
    }

    /**
     * @param int $timeAtPreviousEmployerInMonths
     * @return Peppermint_Dfe_Soap_Application_EmploymentDetails
     */
    public function setTimeAtPreviousEmployerInMonths($timeAtPreviousEmployerInMonths)
    {
        $this->TimeAtPreviousEmployerInMonths = $timeAtPreviousEmployerInMonths;

        return $this;
    }

    /**
     * @return string
     */
    public function getSpouseEmployerName()
    {
        return $this->SpouseEmployerName;
    }

    /**
     * @param string $spouseEmployerName
     * @return Peppermint_Dfe_Soap_Application_EmploymentDetails
     */
    public function setSpouseEmployerName($spouseEmployerName)
    {
        $this->SpouseEmployerName = $spouseEmployerName;

        return $this;
    }

    /**
     * @return string
     */
    public function getSpouseEmploymentStatusCode()
    {
        return $this->SpouseEmploymentStatusCode;
    }

    /**
     * @param string $spouseEmploymentStatusCode
     * @return Peppermint_Dfe_Soap_Application_EmploymentDetails
     */
    public function setSpouseEmploymentStatusCode($spouseEmploymentStatusCode)
    {
        $this->SpouseEmploymentStatusCode = $spouseEmploymentStatusCode;

        return $this;
    }

    /**
     * @return int
     */
    public function getSpouseEmploymentDurationInYears()
    {
        return $this->SpouseEmploymentDurationInYears;
    }

    /**
     * @param int $spouseEmploymentDurationInYears
     * @return Peppermint_Dfe_Soap_Application_EmploymentDetails
     */
    public function setSpouseEmploymentDurationInYears($spouseEmploymentDurationInYears)
    {
        $this->SpouseEmploymentDurationInYears = $spouseEmploymentDurationInYears;

        return $this;
    }

    /**
     * @return int
     */
    public function getSpouseEmploymentDurationInMonths()
    {
        return $this->SpouseEmploymentDurationInMonths;
    }

    /**
     * @param int $spouseEmploymentDurationInMonths
     * @return Peppermint_Dfe_Soap_Application_EmploymentDetails
     */
    public function setSpouseEmploymentDurationInMonths($spouseEmploymentDurationInMonths)
    {
        $this->SpouseEmploymentDurationInMonths = $spouseEmploymentDurationInMonths;

        return $this;
    }
}

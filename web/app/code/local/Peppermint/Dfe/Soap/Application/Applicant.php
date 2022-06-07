<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_Application_Applicant
{
    /**
     * @var string $ApplicantReferenceId
     */
    protected $ApplicantReferenceId = null;

    /**
     * @var string $ApplicantType
     */
    protected $ApplicantType = null;

    /**
     * @return string
     */
    public function getApplicantReferenceId()
    {
        return $this->ApplicantReferenceId;
    }

    /**
     * @param string $applicantReferenceId
     * @return Peppermint_Dfe_Soap_Application_Applicant
     */
    public function setApplicantReferenceId($applicantReferenceId)
    {
        $this->ApplicantReferenceId = $applicantReferenceId;

        return $this;
    }

    /**
     * @return string
     */
    public function getApplicantType()
    {
        return $this->ApplicantType;
    }

    /**
     * @param string $applicantType
     * @return Peppermint_Dfe_Soap_Application_Applicant
     */
    public function setApplicantType($applicantType)
    {
        $this->ApplicantType = $applicantType;

        return $this;
    }
}

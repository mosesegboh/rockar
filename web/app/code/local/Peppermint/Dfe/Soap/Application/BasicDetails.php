<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_Application_BasicDetails
{
    /**
     * @var string $Title
     */
    protected $Title = null;

    /**
     * @var string $FirstName
     */
    protected $FirstName = null;

    /**
     * @var string $LastName
     */
    protected $LastName = null;

    /**
     * @var string $UniqueID
     */
    protected $UniqueID = null;

    /**
     * @var string $UniqueIDType
     */
    protected $UniqueIDType = null;

    /**
     * @var string $DateOfBirth
     */
    protected $DateOfBirth = null;

    /**
     * @var string $Gender
     */
    protected $Gender = null;

    /**
     * @var string $MaritalStatus
     */
    protected $MaritalStatus = null;

    /**
     * @var string $MarriageType
     */
    protected $MarriageType = null;

    /**
     * @var string $CountryOfResidence
     */
    protected $CountryOfResidence = null;

    /**
     * @var string $PreferredLanguage
     */
    protected $PreferredLanguage = null;

    /**
     * @var string $Race
     */
    protected $Race = null;

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
     * @var string $HomeTel
     */
    protected $HomeTel = null;

    /**
     * @var string $Cellphone
     */
    protected $Cellphone = null;

    /**
     * @var string $OfficeNumber
     */
    protected $OfficeNumber = null;

    /**
     * @var string $EmailAddress
     */
    protected $EmailAddress = null;

    /**
     * @var string $KinName
     */
    protected $KinName = null;

    /**
     * @var string $KinTel
     */
    protected $KinTel = null;

    /**
     * @var string $RelationWithKin
     */
    protected $RelationWithKin = null;

    /**
     * @var string $SpouseFirstName
     */
    protected $SpouseFirstName = null;

    /**
     * @var string $SpouseLastName
     */
    protected $SpouseLastName = null;

    /**
     * @var string $SpouseIDNo
     */
    protected $SpouseIDNo = null;

    /**
     * @var string $SpouseIDType
     */
    protected $SpouseIDType = null;

    /**
     * @var string $SpouseCellNumber
     */
    protected $SpouseCellNumber = null;

    /**
     * @var string $SpouseEmail
     */
    protected $SpouseEmail = null;

    /**
     * @var boolean $LiableAsSurety
     */
    protected $LiableAsSurety = null;

    /**
     * @var boolean $LiableAsGaurantor
     */
    protected $LiableAsGaurantor = null;

    /**
     * @var boolean $LiableAsCoDebtor
     */
    protected $LiableAsCoDebtor = null;

    /**
     * @var string $LiableAsComments
     */
    protected $LiableAsComments = null;

    /**
     * @var boolean $SpouseConsent
     */
    protected $SpouseConsent = null;

    /**
     * @param boolean $liableAsSurety
     * @param boolean $liableAsGaurantor
     * @param boolean $liableAsCoDebtor
     * @param boolean $liableAsComments
     * @param boolean $spouseConsent
     */
    public function __construct($liableAsSurety, $liableAsGaurantor, $liableAsCoDebtor, $liableAsComments, $spouseConsent)
    {
        $this->LiableAsSurety = $liableAsSurety;
        $this->LiableAsGaurantor = $liableAsGaurantor;
        $this->LiableAsCoDebtor = $liableAsCoDebtor;
        $this->LiableAsComments = $liableAsComments;
        $this->SpouseConsent = $spouseConsent;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->Title;
    }

    /**
     * @param string $title
     * @return Peppermint_Dfe_Soap_Application_BasicDetails
     */
    public function setTitle($title)
    {
        $this->Title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->FirstName;
    }

    /**
     * @param string $firstName
     * @return Peppermint_Dfe_Soap_Application_BasicDetails
     */
    public function setFirstName($firstName)
    {
        $this->FirstName = $firstName;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->LastName;
    }

    /**
     * @param string $lastName
     * @return Peppermint_Dfe_Soap_Application_BasicDetails
     */
    public function setLastName($lastName)
    {
        $this->LastName = $lastName;

        return $this;
    }

    /**
     * @return string
     */
    public function getUniqueID()
    {
        return $this->UniqueID;
    }

    /**
     * @param string $uniqueID
     * @return Peppermint_Dfe_Soap_Application_BasicDetails
     */
    public function setUniqueID($uniqueID)
    {
        $this->UniqueID = $uniqueID;

        return $this;
    }

    /**
     * @return string
     */
    public function getUniqueIDType()
    {
        return $this->UniqueIDType;
    }

    /**
     * @param string $uniqueIDType
     * @return Peppermint_Dfe_Soap_Application_BasicDetails
     */
    public function setUniqueIDType($uniqueIDType)
    {
        $this->UniqueIDType = $uniqueIDType;

        return $this;
    }

    /**
     * @return string
     */
    public function getDateOfBirth()
    {
        return $this->DateOfBirth;
    }

    /**
     * @param string $dateOfBirth
     * @return Peppermint_Dfe_Soap_Application_BasicDetails
     */
    public function setDateOfBirth($dateOfBirth)
    {
        $this->DateOfBirth = $dateOfBirth;

        return $this;
    }

    /**
     * @return string
     */
    public function getGender()
    {
        return $this->Gender;
    }

    /**
     * @param string $gender
     * @return Peppermint_Dfe_Soap_Application_BasicDetails
     */
    public function setGender($gender)
    {
        $this->Gender = $gender;

        return $this;
    }

    /**
     * @return string
     */
    public function getMaritalStatus()
    {
        return $this->MaritalStatus;
    }

    /**
     * @param string $maritalStatus
     * @return Peppermint_Dfe_Soap_Application_BasicDetails
     */
    public function setMaritalStatus($maritalStatus)
    {
        $this->MaritalStatus = $maritalStatus;

        return $this;
    }

    /**
     * @return string
     */
    public function getMarriageType()
    {
        return $this->MarriageType;
    }

    /**
     * @param string $marriageType
     * @return Peppermint_Dfe_Soap_Application_BasicDetails
     */
    public function setMarriageType($marriageType)
    {
        $this->MarriageType = $marriageType;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountryOfResidence()
    {
        return $this->CountryOfResidence;
    }

    /**
     * @param string $countryOfResidence
     * @return Peppermint_Dfe_Soap_Application_BasicDetails
     */
    public function setCountryOfResidence($countryOfResidence)
    {
        $this->CountryOfResidence = $countryOfResidence;

        return $this;
    }

    /**
     * @return string
     */
    public function getPreferredLanguage()
    {
        return $this->PreferredLanguage;
    }

    /**
     * @param string $preferredLanguage
     * @return Peppermint_Dfe_Soap_Application_BasicDetails
     */
    public function setPreferredLanguage($preferredLanguage)
    {
        $this->PreferredLanguage = $preferredLanguage;

        return $this;
    }

    /**
     * @return string
     */
    public function getRace()
    {
        return $this->Race;
    }

    /**
     * @param string $race
     * @return Peppermint_Dfe_Soap_Application_BasicDetails
     */
    public function setRace($race)
    {
        $this->Race = $race;

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
     * @return Peppermint_Dfe_Soap_Application_BasicDetails
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
     * @return Peppermint_Dfe_Soap_Application_BasicDetails
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
     * @return Peppermint_Dfe_Soap_Application_BasicDetails
     */
    public function setMoTelemarketingBMW($moTelemarketingBMW)
    {
        $this->MO_TELEMARKETING_BMW = $moTelemarketingBMW;

        return $this;
    }

    /**
     * @return string
     */
    public function getHomeTel()
    {
        return $this->HomeTel;
    }

    /**
     * @param string $homeTel
     * @return Peppermint_Dfe_Soap_Application_BasicDetails
     */
    public function setHomeTel($homeTel)
    {
        $this->HomeTel = $homeTel;

        return $this;
    }

    /**
     * @return string
     */
    public function getCellphone()
    {
        return $this->Cellphone;
    }

    /**
     * @param string $cellphone
     * @return Peppermint_Dfe_Soap_Application_BasicDetails
     */
    public function setCellphone($cellphone)
    {
        $this->Cellphone = $cellphone;

        return $this;
    }

    /**
     * @return string
     */
    public function getOfficeNumber()
    {
        return $this->OfficeNumber;
    }

    /**
     * @param string $officeNumber
     * @return Peppermint_Dfe_Soap_Application_BasicDetails
     */
    public function setOfficeNumber($officeNumber)
    {
        $this->OfficeNumber = $officeNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->EmailAddress;
    }

    /**
     * @param string $emailAddress
     * @return Peppermint_Dfe_Soap_Application_BasicDetails
     */
    public function setEmailAddress($emailAddress)
    {
        $this->EmailAddress = $emailAddress;

        return $this;
    }

    /**
     * @return string
     */
    public function getKinName()
    {
        return $this->KinName;
    }

    /**
     * @param string $kinName
     * @return Peppermint_Dfe_Soap_Application_BasicDetails
     */
    public function setKinName($kinName)
    {
        $this->KinName = $kinName;

        return $this;
    }

    /**
     * @return string
     */
    public function getKinTel()
    {
        return $this->KinTel;
    }

    /**
     * @param string $kinTel
     * @return Peppermint_Dfe_Soap_Application_BasicDetails
     */
    public function setKinTel($kinTel)
    {
        $this->KinTel = $kinTel;

        return $this;
    }

    /**
     * @return string
     */
    public function getRelationWithKin()
    {
        return $this->RelationWithKin;
    }

    /**
     * @param string $relationWithKin
     * @return Peppermint_Dfe_Soap_Application_BasicDetails
     */
    public function setRelationWithKin($relationWithKin)
    {
        $this->RelationWithKin = $relationWithKin;

        return $this;
    }

    /**
     * @return string
     */
    public function getSpouseFirstName()
    {
        return $this->SpouseFirstName;
    }

    /**
     * @param string $spouseFirstName
     * @return Peppermint_Dfe_Soap_Application_BasicDetails
     */
    public function setSpouseFirstName($spouseFirstName)
    {
        $this->SpouseFirstName = $spouseFirstName;

        return $this;
    }

    /**
     * @return string
     */
    public function getSpouseLastName()
    {
        return $this->SpouseLastName;
    }

    /**
     * @param string $spouseLastName
     * @return Peppermint_Dfe_Soap_Application_BasicDetails
     */
    public function setSpouseLastName($spouseLastName)
    {
        $this->SpouseLastName = $spouseLastName;

        return $this;
    }

    /**
     * @return string
     */
    public function getSpouseIDNo()
    {
        return $this->SpouseIDNo;
    }

    /**
     * @param string $spouseIDNo
     * @return Peppermint_Dfe_Soap_Application_BasicDetails
     */
    public function setSpouseIDNo($spouseIDNo)
    {
        $this->SpouseIDNo = $spouseIDNo;

        return $this;
    }

    /**
     * @return string
     */
    public function getSpouseIDType()
    {
        return $this->SpouseIDType;
    }

    /**
     * @param string $spouseIDType
     * @return Peppermint_Dfe_Soap_Application_BasicDetails
     */
    public function setSpouseIDType($spouseIDType)
    {
        $this->SpouseIDType = $spouseIDType;

        return $this;
    }

    /**
     * @return string
     */
    public function getSpouseCellNumber()
    {
        return $this->SpouseCellNumber;
    }

    /**
     * @param string $spouseCellNumber
     * @return Peppermint_Dfe_Soap_Application_BasicDetails
     */
    public function setSpouseCellNumber($spouseCellNumber)
    {
        $this->SpouseCellNumber = $spouseCellNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getSpouseEmail()
    {
        return $this->SpouseEmail;
    }

    /**
     * @param string $spouseEmail
     * @return Peppermint_Dfe_Soap_Application_BasicDetails
     */
    public function setSpouseEmail($spouseEmail)
    {
        $this->SpouseEmail = $spouseEmail;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getLiableAsSurety()
    {
        return $this->LiableAsSurety;
    }

    /**
     * @param boolean $liableAsSurety
     * @return Peppermint_Dfe_Soap_Application_BasicDetails
     */
    public function setLiableAsSurety($liableAsSurety)
    {
        $this->LiableAsSurety = $liableAsSurety;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getLiableAsGaurantor()
    {
        return $this->LiableAsGaurantor;
    }

    /**
     * @param boolean $liableAsGaurantor
     * @return Peppermint_Dfe_Soap_Application_BasicDetails
     */
    public function setLiableAsGaurantor($liableAsGaurantor)
    {
        $this->LiableAsGaurantor = $liableAsGaurantor;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getLiableAsCoDebtor()
    {
        return $this->LiableAsCoDebtor;
    }

    /**
     * @param boolean $liableAsCoDebtor
     * @return Peppermint_Dfe_Soap_Application_BasicDetails
     */
    public function setLiableAsCoDebtor($liableAsCoDebtor)
    {
        $this->LiableAsCoDebtor = $liableAsCoDebtor;

        return $this;
    }

    /**
     * @return string
     */
    public function getLiableAsComments()
    {
        return $this->LiableAsComments;
    }

    /**
     * @param string $liableAsComments
     * @return Peppermint_Dfe_Soap_Application_BasicDetails
     */
    public function setLiableAsComments($liableAsComments)
    {
        $this->LiableAsComments = $liableAsComments;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getSpouseConsent()
    {
        return $this->SpouseConsent;
    }

    /**
     * @param boolean $spouseConsent
     * @return Peppermint_Dfe_Soap_Application_BasicDetails
     */
    public function setSpouseConsent($spouseConsent)
    {
        $this->SpouseConsent = $spouseConsent;

        return $this;
    }
}

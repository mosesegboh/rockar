<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_Application_AssetDetails
{
    /**
     * @var string $AssetId
     */
    protected $AssetId = null;

    /**
     * @var string $ChassisNo
     */
    protected $ChassisNo = null;

    /**
     * @var string $EngineNumber
     */
    protected $EngineNumber = null;

    /**
     * @var int $CurrentMileage
     */
    protected $CurrentMileage = null;

    /**
     * @var string $AssetStatus
     */
    protected $AssetStatus = null;

    /**
     * @var Peppermint_Dfe_Soap_Application_ArrayOfAssetOptionsDetails $AssetOptionsList
     */
    protected $AssetOptionsList = null;

    /**
     * @return string
     */
    public function getAssetId()
    {
        return $this->AssetId;
    }

    /**
     * @param string $assetId
     * @return Peppermint_Dfe_Soap_Application_AssetDetails
     */
    public function setAssetId($assetId)
    {
        $this->AssetId = $assetId;

        return $this;
    }

    /**
     * @return string
     */
    public function getChassisNo()
    {
        return $this->ChassisNo;
    }

    /**
     * @param string $chassisNo
     * @return Peppermint_Dfe_Soap_Application_AssetDetails
     */
    public function setChassisNo($chassisNo)
    {
        $this->ChassisNo = $chassisNo;

        return $this;
    }

    /**
     * @return string
     */
    public function getEngineNumber()
    {
        return $this->EngineNumber;
    }

    /**
     * @param string $engineNumber
     * @return Peppermint_Dfe_Soap_Application_AssetDetails
     */
    public function setEngineNumber($engineNumber)
    {
        $this->EngineNumber = $engineNumber;

        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentMileage()
    {
        return $this->CurrentMileage;
    }

    /**
     * @param int $currentMileage
     * @return Peppermint_Dfe_Soap_Application_AssetDetails
     */
    public function setCurrentMileage($currentMileage)
    {
        $this->CurrentMileage = $currentMileage;

        return $this;
    }

    /**
     * @return string
     */
    public function getAssetStatus()
    {
        return $this->AssetStatus;
    }

    /**
     * @param string $assetStatus
     * @return Peppermint_Dfe_Soap_Application_AssetDetails
     */
    public function setAssetStatus($assetStatus)
    {
        $this->AssetStatus = $assetStatus;

        return $this;
    }

    /**
     * @return Peppermint_Dfe_Soap_Application_ArrayOfAssetOptionsDetails
     */
    public function getAssetOptionsList()
    {
        return $this->AssetOptionsList;
    }

    /**
     * @param Peppermint_Dfe_Soap_Application_ArrayOfAssetOptionsDetails $assetOptionsList
     * @return Peppermint_Dfe_Soap_Application_AssetDetails
     */
    public function setAssetOptionsList($assetOptionsList)
    {
        $this->AssetOptionsList = $assetOptionsList;

        return $this;
    }
}

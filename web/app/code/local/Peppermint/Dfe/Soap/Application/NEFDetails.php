<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_Application_NEFDetails
{
    /**
     * @var float $SettlementValue
     */
    protected $SettlementValue = null;

    /**
     * @var float $TradeInValue
     */
    protected $TradeInValue = null;

    /**
     * @var float $ShortfallAllowance
     */
    protected $ShortfallAllowance = null;

    /**
     * @var float $NEFAmt
     */
    protected $NEFAmt = null;

    /**
     * @var string $Make
     */
    protected $Make = null;

    /**
     * @var string $Model
     */
    protected $Model = null;

    /**
     * @var int $Year
     */
    protected $Year = null;

    /**
     * @var string $ChassisNo
     */
    protected $ChassisNo = null;

    /**
     * @return float
     */
    public function getSettlementValue()
    {
        return $this->SettlementValue;
    }

    /**
     * @param float $settlementValue
     * @return Peppermint_Dfe_Soap_Application_NEFDetails
     */
    public function setSettlementValue($settlementValue)
    {
        $this->SettlementValue = $settlementValue;

        return $this;
    }

    /**
     * @return float
     */
    public function getTradeInValue()
    {
        return $this->TradeInValue;
    }

    /**
     * @param float $tradeInValue
     * @return Peppermint_Dfe_Soap_Application_NEFDetails
     */
    public function setTradeInValue($tradeInValue)
    {
        $this->TradeInValue = $tradeInValue;

        return $this;
    }

    /**
     * @return float
     */
    public function getShortfallAllowance()
    {
        return $this->ShortfallAllowance;
    }

    /**
     * @param float $shortfallAllowance
     * @return Peppermint_Dfe_Soap_Application_NEFDetails
     */
    public function setShortfallAllowance($shortfallAllowance)
    {
        $this->ShortfallAllowance = $shortfallAllowance;

        return $this;
    }

    /**
     * @return float
     */
    public function getNEFAmt()
    {
        return $this->NEFAmt;
    }

    /**
     * @param float $NEFAmt
     * @return Peppermint_Dfe_Soap_Application_NEFDetails
     */
    public function setNEFAmt($NEFAmt)
    {
        $this->NEFAmt = $NEFAmt;

        return $this;
    }

    /**
     * @return string
     */
    public function getMake()
    {
        return $this->Make;
    }

    /**
     * @param string $make
     * @return Peppermint_Dfe_Soap_Application_NEFDetails
     */
    public function setMake($make)
    {
        $this->Make = $make;

        return $this;
    }

    /**
     * @return string
     */
    public function getModel()
    {
        return $this->Model;
    }

    /**
     * @param string $model
     * @return Peppermint_Dfe_Soap_Application_NEFDetails
     */
    public function setModel($model)
    {
        $this->Model = $model;

        return $this;
    }

    /**
     * @return int
     */
    public function getYear()
    {
        return $this->Year;
    }

    /**
     * @param int $year
     * @return Peppermint_Dfe_Soap_Application_NEFDetails
     */
    public function setYear($year)
    {
        $this->Year = $year;

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
     * @return Peppermint_Dfe_Soap_Application_NEFDetails
     */
    public function setChassisNo($chassisNo)
    {
        $this->ChassisNo = $chassisNo;

        return $this;
    }
}

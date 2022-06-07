<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_Application_FeeDetails
{
    /**
     * @var string $FeeName
     */
    protected $FeeName = null;

    /**
     * @var float $FeeAmount
     */
    protected $FeeAmount = null;

    /**
     * @param float $feeAmount
     * @param string $feeName
     */
    public function __construct($feeAmount, $feeName)
    {
        $this->FeeAmount = $feeAmount;
        $this->FeeName = $feeName;
    }

    /**
     * @return string
     */
    public function getFeeName()
    {
        return $this->FeeName;
    }

    /**
     * @param string $feeName
     * @return Peppermint_Dfe_Soap_Application_FeeDetails
     */
    public function setFeeName($feeName)
    {
        $this->FeeName = $feeName;

        return $this;
    }

    /**
     * @return float
     */
    public function getFeeAmount()
    {
        return $this->FeeAmount;
    }

    /**
     * @param float $feeAmount
     * @return Peppermint_Dfe_Soap_Application_FeeDetails
     */
    public function setFeeAmount($feeAmount)
    {
        $this->FeeAmount = $feeAmount;

        return $this;
    }
}

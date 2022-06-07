<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_Application_AssetOptionsDetails
{
    /**
     * @var string $OptionDescription
     */
    protected $OptionDescription = null;

    /**
     * @var float $Price
     */
    protected $Price = null;

    /**
     * @param float $price
     */
    public function __construct($price)
    {
        $this->Price = $price;
    }

    /**
     * @return string
     */
    public function getOptionDescription()
    {
        return $this->OptionDescription;
    }

    /**
     * @param string $optionDescription
     * @return Peppermint_Dfe_Soap_Application_AssetOptionsDetails
     */
    public function setOptionDescription($optionDescription)
    {
        $this->OptionDescription = $optionDescription;

        return $this;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->Price;
    }

    /**
     * @param float $price
     * @return Peppermint_Dfe_Soap_Application_AssetOptionsDetails
     */
    public function setPrice($price)
    {
        $this->Price = $price;

        return $this;
    }
}

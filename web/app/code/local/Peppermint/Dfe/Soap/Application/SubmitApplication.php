<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_Application_SubmitApplication
{
    /**
     * @var Peppermint_Dfe_Soap_Application_FinanceApplication $InputApp
     */
    protected $InputApp = null;

    /**
     * @param Peppermint_Dfe_Soap_Application_FinanceApplication $inputApp
     */
    public function __construct($inputApp)
    {
        $this->InputApp = $inputApp;
    }

    /**
     * @return Peppermint_Dfe_Soap_Application_FinanceApplication
     */
    public function getInputApp()
    {
        return $this->InputApp;
    }

    /**
     * @param Peppermint_Dfe_Soap_Application_FinanceApplication $inputApp
     * @return Peppermint_Dfe_Soap_Application_SubmitApplication
     */
    public function setInputApp($inputApp)
    {
        $this->InputApp = $inputApp;

        return $this;
    }
}

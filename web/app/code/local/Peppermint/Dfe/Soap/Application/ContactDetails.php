<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_Application_ContactDetails
{
    /**
     * @var boolean $PrefMethodContactEmail
     */
    protected $PrefMethodContactEmail = null;

    /**
     * @var boolean $PrefMethodContactSMS
     */
    protected $PrefMethodContactSMS = null;

    /**
     * @var boolean $PrefMethodContactNormal
     */
    protected $PrefMethodContactNormal = null;

    /**
     * @var string $ContractDoc
     */
    protected $ContractDoc = null;

    /**
     * @var string $StatementFreq
     */
    protected $StatementFreq = null;

    /**
     * @return boolean
     */
    public function getPrefMethodContactEmail()
    {
        return $this->PrefMethodContactEmail;
    }

    /**
     * @param boolean $prefMethodContactEmail
     * @return Peppermint_Dfe_Soap_Application_ContactDetails
     */
    public function setPrefMethodContactEmail($prefMethodContactEmail)
    {
        $this->PrefMethodContactEmail = $prefMethodContactEmail;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getPrefMethodContactSMS()
    {
        return $this->PrefMethodContactSMS;
    }

    /**
     * @param boolean $prefMethodContactSMS
     * @return Peppermint_Dfe_Soap_Application_ContactDetails
     */
    public function setPrefMethodContactSMS($prefMethodContactSMS)
    {
        $this->PrefMethodContactSMS = $prefMethodContactSMS;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getPrefMethodContactNormal()
    {
        return $this->PrefMethodContactNormal;
    }

    /**
     * @param boolean $prefMethodContactNormal
     * @return Peppermint_Dfe_Soap_Application_ContactDetails
     */
    public function setPrefMethodContactNormal($prefMethodContactNormal)
    {
        $this->PrefMethodContactNormal = $prefMethodContactNormal;

        return $this;
    }

    /**
     * @return string
     */
    public function getContractDoc()
    {
        return $this->ContractDoc;
    }

    /**
     * @param string $contractDoc
     * @return Peppermint_Dfe_Soap_Application_ContactDetails
     */
    public function setContractDoc($contractDoc)
    {
        $this->ContractDoc = $contractDoc;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatementFreq()
    {
        return $this->StatementFreq;
    }

    /**
     * @param string $statementFreq
     * @return Peppermint_Dfe_Soap_Application_ContactDetails
     */
    public function setStatementFreq($statementFreq)
    {
        $this->StatementFreq = $statementFreq;

        return $this;
    }
}

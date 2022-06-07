<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_Application_FinanceApplicationResponse
{
    /**
     * @var boolean $Success
     */
    protected $Success = null;

    /**
     * @var Peppermint_Dfe_Soap_Application_ArrayOfString $Messages
     */
    protected $Messages = null;

    /**
     * @var Peppermint_Dfe_Soap_Application_ArrayOfValidationMessages $ValidationMessages
     */
    protected $ValidationMessages = null;

    /**
     * @param boolean $Success
     */
    public function __construct($success)
    {
        $this->Success = $success;
    }

    /**
     * @return boolean
     */
    public function getSuccess()
    {
        return $this->Success;
    }

    /**
     * @param boolean $success
     * @return Peppermint_Dfe_Soap_Application_FinanceApplicationResponse
     */
    public function setSuccess($success)
    {
        $this->Success = $success;

        return $this;
    }

    /**
     * @return Peppermint_Dfe_Soap_Application_ArrayOfString
     */
    public function getMessages()
    {
        return $this->Messages;
    }

    /**
     * @param Peppermint_Dfe_Soap_Application_ArrayOfString $messages
     * @return Peppermint_Dfe_Soap_Application_FinanceApplicationResponse
     */
    public function setMessages($messages)
    {
        $this->Messages = $messages;

        return $this;
    }

    /**
     * @return Peppermint_Dfe_Soap_Application_ArrayOfValidationMessages
     */
    public function getValidationMessages()
    {
        return $this->ValidationMessages;
    }

    /**
     * @param Peppermint_Dfe_Soap_Application_ArrayOfValidationMessages $validationMessages
     * @return Peppermint_Dfe_Soap_Application_FinanceApplicationResponse
     */
    public function setValidationMessages($validationMessages)
    {
        $this->ValidationMessages = $validationMessages;

        return $this;
    }
}

<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_Application_ValidationMessages
{
    /**
     * @var string $PropertyName
     */
    protected $PropertyName = null;

    /**
     * @var Peppermint_Dfe_Soap_Application_ArrayOfString $Messages
     */
    protected $Messages = null;

    /**
     * @return string
     */
    public function getPropertyName()
    {
        return $this->PropertyName;
    }

    /**
     * @param string $propertyName
     * @return Peppermint_Dfe_Soap_Application_ValidationMessages
     */
    public function setPropertyName($propertyName)
    {
        $this->PropertyName = $propertyName;

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
     * @return Peppermint_Dfe_Soap_Application_ValidationMessages
     */
    public function setMessages($messages)
    {
        $this->Messages = $messages;

        return $this;
    }
}

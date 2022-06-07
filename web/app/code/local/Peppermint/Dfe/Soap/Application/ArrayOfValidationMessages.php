<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_Application_ArrayOfValidationMessages implements \ArrayAccess, \Iterator, \Countable
{
    /**
     * @var Peppermint_Dfe_Soap_Application_ValidationMessages[] $ValidationMessages
     */
    protected $ValidationMessages = null;

    /**
     * @return Peppermint_Dfe_Soap_Application_ValidationMessages[]
     */
    public function getValidationMessages()
    {
        return $this->ValidationMessages;
    }

    /**
     * @param Peppermint_Dfe_Soap_Application_ValidationMessages[] $validationMessages
     * @return Peppermint_Dfe_Soap_Application_ArrayOfValidationMessages
     */
    public function setValidationMessages(array $validationMessages = null)
    {
        $this->ValidationMessages = $validationMessages;

        return $this;
    }

    /**
     * ArrayAccess implementation
     *
     * @param mixed $offset An offset to check for
     * @return boolean true on success or false on failure
     */
    public function offsetExists($offset)
    {
        return isset($this->ValidationMessages[$offset]);
    }

    /**
     * ArrayAccess implementation
     *
     * @param mixed $offset The offset to retrieve
     * @return Peppermint_Dfe_Soap_Application_ValidationMessages
     */
    public function offsetGet($offset)
    {
        return $this->ValidationMessages[$offset];
    }

    /**
     * ArrayAccess implementation
     *
     * @param mixed $offset The offset to assign the value to
     * @param Peppermint_Dfe_Soap_Application_ValidationMessages $value The value to set
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (!isset($offset)) {
            $this->ValidationMessages[] = $value;
        } else {
            $this->ValidationMessages[$offset] = $value;
        }
    }

    /**
     * ArrayAccess implementation
     *
     * @param mixed $offset The offset to unset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->ValidationMessages[$offset]);
    }

    /**
     * Iterator implementation
     *
     * @return Peppermint_Dfe_Soap_Application_ValidationMessages Return the current element
     */
    public function current()
    {
        return current($this->ValidationMessages);
    }

    /**
     * Iterator implementation
     * Move forward to next element
     *
     * @return void
     */
    public function next()
    {
        next($this->ValidationMessages);
    }

    /**
     * Iterator implementation
     *
     * @return string|null Return the key of the current element or null
     */
    public function key()
    {
        return key($this->ValidationMessages);
    }

    /**
     * Iterator implementation
     *
     * @return boolean Return the validity of the current position
     */
    public function valid()
    {
        return $this->key() !== null;
    }

    /**
     * Iterator implementation
     * Rewind the Iterator to the first element
     *
     * @return void
     */
    public function rewind()
    {
        reset($this->ValidationMessages);
    }

    /**
     * Countable implementation
     *
     * @return Peppermint_Dfe_Soap_Application_ValidationMessages Return count of elements
     */
    public function count()
    {
        return count($this->ValidationMessages);
    }
}

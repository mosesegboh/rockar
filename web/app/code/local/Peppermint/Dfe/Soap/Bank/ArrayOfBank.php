<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Robert Ionas <robert.ionas@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_Bank_ArrayOfBank implements \ArrayAccess, \Iterator, \Countable
{
    /**
     * @var Peppermint_Dfe_Soap_Bank_Bank[] $Bank
     */
    protected $Bank = null;

    /**
     * @return Peppermint_Dfe_Soap_Bank_Bank[]
     */
    public function getBank()
    {
        return $this->Bank;
    }

    /**
     * @param Peppermint_Dfe_Soap_Bank_Bank[] $bank
     * @return Peppermint_Dfe_Soap_Bank_ArrayOfBank
     */
    public function setBank(array $bank = null)
    {
        $this->Bank = $bank;

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
        return isset($this->Bank[$offset]);
    }

    /**
     * ArrayAccess implementation
     *
     * @param mixed $offset The offset to retrieve
     * @return Peppermint_Dfe_Soap_Bank_Bank
     */
    public function offsetGet($offset)
    {
        return $this->Bank[$offset];
    }

    /**
     * ArrayAccess implementation
     *
     * @param mixed $offset The offset to assign the value to
     * @param Peppermint_Dfe_Soap_Bank_Bank $value The value to set
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (!isset($offset)) {
            $this->Bank[] = $value;
        } else {
            $this->Bank[$offset] = $value;
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
        unset($this->Bank[$offset]);
    }

    /**
     * Iterator implementation
     *
     * @return Peppermint_Dfe_Soap_Bank_Bank Return the current element
     */
    public function current()
    {
        return current($this->Bank);
    }

    /**
     * Iterator implementation
     * Move forward to next element
     *
     * @return void
     */
    public function next()
    {
        next($this->Bank);
    }

    /**
     * Iterator implementation
     *
     * @return string|null Return the key of the current element or null
     */
    public function key()
    {
        return key($this->Bank);
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
        reset($this->Bank);
    }

    /**
     * Countable implementation
     *
     * @return Peppermint_Dfe_Soap_Bank_Bank Return count of elements
     */
    public function count()
    {
        return count($this->Bank);
    }
}

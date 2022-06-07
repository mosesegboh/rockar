<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_Application_ArrayOfFeeDetails implements \ArrayAccess, \Iterator, \Countable
{
    /**
     * @var Peppermint_Dfe_Soap_Application_FeeDetails[] $FeeDetails
     */
    protected $FeeDetails = null;

    /**
     * @return Peppermint_Dfe_Soap_Application_FeeDetails[]
     */
    public function getFeeDetails()
    {
        return $this->FeeDetails;
    }

    /**
     * @param Peppermint_Dfe_Soap_Application_FeeDetails[] $feeDetails
     * @return Peppermint_Dfe_Soap_Application_ArrayOfFeeDetails
     */
    public function setFeeDetails(array $feeDetails = null)
    {
        $this->FeeDetails = $feeDetails;

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
        return isset($this->FeeDetails[$offset]);
    }

    /**
     * ArrayAccess implementation
     *
     * @param mixed $offset The offset to retrieve
     * @return Peppermint_Dfe_Soap_Application_FeeDetails
     */
    public function offsetGet($offset)
    {
        return $this->FeeDetails[$offset];
    }

    /**
     * ArrayAccess implementation
     *
     * @param mixed $offset The offset to assign the value to
     * @param Peppermint_Dfe_Soap_Application_FeeDetails $value The value to set
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (!isset($offset)) {
            $this->FeeDetails[] = $value;
        } else {
            $this->FeeDetails[$offset] = $value;
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
        unset($this->FeeDetails[$offset]);
    }

    /**
     * Iterator implementation
     *
     * @return Peppermint_Dfe_Soap_Application_FeeDetails Return the current element
     */
    public function current()
    {
        return current($this->FeeDetails);
    }

    /**
     * Iterator implementation
     * Move forward to next element
     *
     * @return void
     */
    public function next()
    {
        next($this->FeeDetails);
    }

    /**
     * Iterator implementation
     *
     * @return string|null Return the key of the current element or null
     */
    public function key()
    {
        return key($this->FeeDetails);
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
        reset($this->FeeDetails);
    }

    /**
     * Countable implementation
     *
     * @return Peppermint_Dfe_Soap_Application_FeeDetails Return count of elements
     */
    public function count()
    {
        return count($this->FeeDetails);
    }
}

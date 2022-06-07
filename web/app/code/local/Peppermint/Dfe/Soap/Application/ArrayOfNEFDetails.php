<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_Application_ArrayOfNEFDetails implements \ArrayAccess, \Iterator, \Countable
{
    /**
     * @var Peppermint_Dfe_Soap_Application_NEFDetails[] $NEFDetails
     */
    protected $NEFDetails = null;

    /**
     * @return Peppermint_Dfe_Soap_Application_NEFDetails[]
     */
    public function getNEFDetails()
    {
        return $this->NEFDetails;
    }

    /**
     * @param Peppermint_Dfe_Soap_Application_NEFDetails[] $nefDetails
     * @return Peppermint_Dfe_Soap_Application_ArrayOfNEFDetails
     */
    public function setNEFDetails(array $nefDetails = null)
    {
        $this->NEFDetails = $nefDetails;

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
        return isset($this->NEFDetails[$offset]);
    }

    /**
     * ArrayAccess implementation
     *
     * @param mixed $offset The offset to retrieve
     * @return Peppermint_Dfe_Soap_Application_NEFDetails
     */
    public function offsetGet($offset)
    {
        return $this->NEFDetails[$offset];
    }

    /**
     * ArrayAccess implementation
     *
     * @param mixed $offset The offset to assign the value to
     * @param Peppermint_Dfe_Soap_Application_NEFDetails $value The value to set
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (!isset($offset)) {
            $this->NEFDetails[] = $value;
        } else {
            $this->NEFDetails[$offset] = $value;
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
        unset($this->NEFDetails[$offset]);
    }

    /**
     * Iterator implementation
     *
     * @return Peppermint_Dfe_Soap_Application_NEFDetails Return the current element
     */
    public function current()
    {
        return current($this->NEFDetails);
    }

    /**
     * Iterator implementation
     * Move forward to next element
     *
     * @return void
     */
    public function next()
    {
        next($this->NEFDetails);
    }

    /**
     * Iterator implementation
     *
     * @return string|null Return the key of the current element or null
     */
    public function key()
    {
        return key($this->NEFDetails);
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
        reset($this->NEFDetails);
    }

    /**
     * Countable implementation
     *
     * @return Peppermint_Dfe_Soap_Application_NEFDetails Return count of elements
     */
    public function count()
    {
        return count($this->NEFDetails);
    }
}

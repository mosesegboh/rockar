<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_ReferenceCodes_ArrayOfReferenceCode implements \ArrayAccess, \Iterator, \Countable
{
    /**
     * @var Peppermint_Dfe_Soap_ReferenceCodes_ReferenceCode[] $ReferenceCode
     */
    protected $ReferenceCode = null;

    /**
     * @return Peppermint_Dfe_Soap_ReferenceCodes_ReferenceCode[]
     */
    public function getReferenceCode()
    {
        return $this->ReferenceCode;
    }

    /**
     * @param Peppermint_Dfe_Soap_ReferenceCodes_ReferenceCode[] $referenceCode
     * @return Peppermint_Dfe_Soap_ReferenceCodes_ArrayOfReferenceCode
     */
    public function setReferenceCode(array $referenceCode = null)
    {
        $this->ReferenceCode = $referenceCode;

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
        return isset($this->ReferenceCode[$offset]);
    }

    /**
     * ArrayAccess implementation
     *
     * @param mixed $offset The offset to retrieve
     * @return Peppermint_Dfe_Soap_ReferenceCodes_ReferenceCode
     */
    public function offsetGet($offset)
    {
        return $this->ReferenceCode[$offset];
    }

    /**
     * ArrayAccess implementation
     *
     * @param mixed $offset The offset to assign the value to
     * @param Peppermint_Dfe_Soap_ReferenceCodes_ReferenceCode $value The value to set
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (!isset($offset)) {
            $this->ReferenceCode[] = $value;
        } else {
            $this->ReferenceCode[$offset] = $value;
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
        unset($this->ReferenceCode[$offset]);
    }

    /**
     * Iterator implementation
     *
     * @return Peppermint_Dfe_Soap_ReferenceCodes_ReferenceCode Return the current element
     */
    public function current()
    {
        return current($this->ReferenceCode);
    }

    /**
     * Iterator implementation
     * Move forward to next element
     *
     * @return void
     */
    public function next()
    {
        next($this->ReferenceCode);
    }

    /**
     * Iterator implementation
     *
     * @return string|null Return the key of the current element or null
     */
    public function key()
    {
        return key($this->ReferenceCode);
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
        reset($this->ReferenceCode);
    }

    /**
     * Countable implementation
     *
     * @return Peppermint_Dfe_Soap_ReferenceCodes_ReferenceCode Return count of elements
     */
    public function count()
    {
        return count($this->ReferenceCode);
    }
}

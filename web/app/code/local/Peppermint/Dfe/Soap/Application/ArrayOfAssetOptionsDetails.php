<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_Application_ArrayOfAssetOptionsDetails implements \ArrayAccess, \Iterator, \Countable
{
    /**
     * @var Peppermint_Dfe_Soap_Application_AssetOptionsDetails[] $AssetOptionsDetails
     */
    protected $AssetOptionsDetails = null;

    /**
     * @return Peppermint_Dfe_Soap_Application_AssetOptionsDetails[]
     */
    public function getAssetOptionsDetails()
    {
        return $this->AssetOptionsDetails;
    }

    /**
     * @param Peppermint_Dfe_Soap_Application_AssetOptionsDetails[] $assetOptionsDetails
     * @return Peppermint_Dfe_Soap_Application_ArrayOfAssetOptionsDetails
     */
    public function setAssetOptionsDetails(array $assetOptionsDetails = null)
    {
        $this->AssetOptionsDetails = $assetOptionsDetails;

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
        return isset($this->AssetOptionsDetails[$offset]);
    }

    /**
     * ArrayAccess implementation
     *
     * @param mixed $offset The offset to retrieve
     * @return Peppermint_Dfe_Soap_Application_AssetOptionsDetails
     */
    public function offsetGet($offset)
    {
        return $this->AssetOptionsDetails[$offset];
    }

    /**
     * ArrayAccess implementation
     *
     * @param mixed $offset The offset to assign the value to
     * @param Peppermint_Dfe_Soap_Application_AssetOptionsDetails $value The value to set
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (!isset($offset)) {
            $this->AssetOptionsDetails[] = $value;
        } else {
            $this->AssetOptionsDetails[$offset] = $value;
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
        unset($this->AssetOptionsDetails[$offset]);
    }

    /**
     * Iterator implementation
     *
     * @return Peppermint_Dfe_Soap_Application_AssetOptionsDetails Return the current element
     */
    public function current()
    {
        return current($this->AssetOptionsDetails);
    }

    /**
     * Iterator implementation
     * Move forward to next element
     *
     * @return void
     */
    public function next()
    {
        next($this->AssetOptionsDetails);
    }

    /**
     * Iterator implementation
     *
     * @return string|null Return the key of the current element or null
     */
    public function key()
    {
        return key($this->AssetOptionsDetails);
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
        reset($this->AssetOptionsDetails);
    }

    /**
     * Countable implementation
     *
     * @return Peppermint_Dfe_Soap_Application_AssetOptionsDetails Return count of elements
     */
    public function count()
    {
        return count($this->AssetOptionsDetails);
    }
}

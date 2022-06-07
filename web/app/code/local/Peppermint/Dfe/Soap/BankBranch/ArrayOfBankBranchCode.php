<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Robert Ionas <robert.ionas@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Soap_BankBranch_ArrayOfBankBranchCode implements \ArrayAccess, \Iterator, \Countable
{
    /**
     * @var Peppermint_Dfe_Soap_BankBranch_BankBranchCode[] $BankBranchCode
     */
    protected $BankBranchCode = null;

    /**
     * @return Peppermint_Dfe_Soap_BankBranch_BankBranchCode[]
     */
    public function getBank()
    {
        return $this->BankBranchCode;
    }

    /**
     * @param Peppermint_Dfe_Soap_BankBranch_BankBranchCode[] $bankBranchCode
     * @return Peppermint_Dfe_Soap_BankBranch_ArrayOfBankBranchCode
     */
    public function setBank(array $bankBranchCode = null)
    {
        $this->BankBranchCode = $bankBranchCode;

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
        return isset($this->BankBranchCode[$offset]);
    }

    /**
     * ArrayAccess implementation
     *
     * @param mixed $offset The offset to retrieve
     * @return Peppermint_Dfe_Soap_BankBranch_BankBranchCode
     */
    public function offsetGet($offset)
    {
        return $this->BankBranchCode[$offset];
    }

    /**
     * ArrayAccess implementation
     *
     * @param mixed $offset The offset to assign the value to
     * @param Peppermint_Dfe_Soap_BankBranch_BankBranchCode $value The value to set
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (!isset($offset)) {
            $this->BankBranchCode[] = $value;
        } else {
            $this->BankBranchCode[$offset] = $value;
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
        unset($this->BankBranchCode[$offset]);
    }

    /**
     * Iterator implementation
     *
     * @return Peppermint_Dfe_Soap_BankBranch_BankBranchCode Return the current element
     */
    public function current()
    {
        return current($this->BankBranchCode);
    }

    /**
     * Iterator implementation
     * Move forward to next element
     *
     * @return void
     */
    public function next()
    {
        next($this->BankBranchCode);
    }

    /**
     * Iterator implementation
     *
     * @return string|null Return the key of the current element or null
     */
    public function key()
    {
        return key($this->BankBranchCode);
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
        reset($this->BankBranchCode);
    }

    /**
     * Countable implementation
     *
     * @return Peppermint_Dfe_Soap_BankBranch_BankBranchCode Return count of elements
     */
    public function count()
    {
        return count($this->BankBranchCode);
    }
}

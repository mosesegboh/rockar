<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Dfe
 * @author    Robert Ionas <robert.ionas@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Dfe_Model_Bank_Branches extends Mage_Core_Model_Abstract
{
    protected $_eventPrefix = 'peppermint_dfe_bank_branches';

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('peppermint_dfe/bank_branches');
    }

    /**
     * Performs database sync based on the provided collection of foreign bank branches
     *
     * @param Peppermint_Dfe_Soap_BankBranch_ArrayOfBankBranchCode $bankBranches
     *
     * @return Peppermint_Dfe_Model_Bank_Branches $this
     */
    public function sync(Peppermint_Dfe_Soap_BankBranch_ArrayOfBankBranchCode $bankBranches)
    {
        $rows = [];
        /** @var Peppermint_Dfe_Model_Resource_Bank_Branches $resource */
        $resource = $this->getResource();

        $bankBranches->rewind();
        for ($bankBranches->current(); $bankBranches->valid(); $bankBranches->next()) {
            $rows[] = $bankBranches->current()->getData();
        }

        if (!empty($rows)) {
            $resource->sync($rows);
        }

        return $this;
    }

    /**
     * Retrieve the array of branches.
     *
     * @param Peppermint_Dfe_Soap_BankBranch_ArrayOfBankBranchCode $bankBranches
     * @param string $bankCode
     *
     * @return array
     */
    public function getTheArrayOfBranches(Peppermint_Dfe_Soap_BankBranch_ArrayOfBankBranchCode $bankBranches, $bankCode)
    {
        $result = [];

        while ($bankBranches->valid()) {
            $curr = $bankBranches->current();

            if ($curr->getBankCode() == $bankCode) {
                $result[] = [
                    'value' => trim($curr->getBranchCode()),
                    'text' => trim($curr->getBranchName())
                ];
            }
            $bankBranches->next();
        }

        return $result;
    }
}

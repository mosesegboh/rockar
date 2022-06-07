<?php

/**
 * @category     Peppermint
 * @package      Peppermint\Dfe
 * @author       Robert Ionas <robert.ionas@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Dfe_Model_Bank_Bank extends Mage_Core_Model_Abstract
{
    /**
     * Retrieve the array of banks.
     *
     * @param Peppermint_Dfe_Soap_Bank_ArrayOfBank $banks
     * @return array
     */
    public function getTheArrayOfBanks(Peppermint_Dfe_Soap_Bank_ArrayOfBank $banks)
    {
        $result = [];
        while ($banks->valid()) {
            $result[] = [
                'value' => trim($banks->current()->getBankCode()),
                'text' => trim($banks->current()->getBankName())
            ];
            $banks->next();
        }

        return $result;
    }
}

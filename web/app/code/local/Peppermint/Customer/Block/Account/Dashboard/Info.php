<?php

/**
 * @category Peppermint
 * @package Peppermint\Customer
 * @author Mihai Chezan <mihai.chezan@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Customer_Block_Account_Dashboard_Info extends Rockar_Customer_Block_Account_Dashboard_Info
{
    /**
     * Get available south african document types
     *
     * @return string
     */
    public function getSouthAfricanDocumentTypesJson()
    {
        return Mage::helper('peppermint_customer')->getSouthAfricanDocumentTypesJson();
    }

    /**
     * Get all countries
     *
     * @return string
     */
    public function getAllCountriesJson()
    {
        $countryList = Mage::getModel('directory/country')->getResourceCollection()
            ->toOptionArray();

        return Mage::helper('rockar_all')->jsonEncode($countryList);
    }
}

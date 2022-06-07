<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Alexander Metzgen <alexander.metzgen@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_FinancingOptions_Block_FinanceQuote extends Rockar2_FinancingOptions_Block_FinanceQuote
{
    /**
     * Get instalment group id for current store.
     *
     * @return string|null
     */
    public function getInstalmentGroupId()
    {
        return Mage::helper('peppermint_financingoptions')->getGroupIdByMethodType(
            Peppermint_FinancingOptions_Model_Adminhtml_System_Config_Source_MethodType::TYPE_INSTALMENT
        );
    }

    /**
     * Get part exchange data
     *
     * @return array
     */
    public function getPartExchangeData()
    {
        return Mage::helper('financing_options')->getPartExchangeData();
    }

    /**
     * Get part exchange additional data
     *
     * @return array
     */
    public function getAdditionalPartExchangeDataNonJson()
    {
        return Mage::helper('financing_options')->getAdditionalPartExchangeDataNonJson();
    }
}

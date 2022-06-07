<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Alexander Metzgen <alexander.metzgen@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_FinancingOptions_Block_Checkout_Steps_Finance extends Rockar_FinancingOptions_Block_Checkout_Steps_Finance
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
}

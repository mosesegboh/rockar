<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Jiraphong Witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

class Peppermint_FinancingOptions_Model_Options extends Rockar_FinancingOptions_Model_Options
{
    /**
     * Validate current payment is instalment.
     *
     * @return boolean
     */
    public function isInstalment()
    {
        return $this->getMethodType()
            == Peppermint_FinancingOptions_Model_Adminhtml_System_Config_Source_MethodType::TYPE_INSTALMENT;
    }

    /**
     * Get protected model alias from parent
     *
     * @return string
     */
    public function getFinanceTermsModelAlias()
    {
        return $this->_financeTermsModelAlias;
    }
}

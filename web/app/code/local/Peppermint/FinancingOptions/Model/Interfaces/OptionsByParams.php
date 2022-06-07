<?php

/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

/**
 * Class Peppermint_FinancingOptions_Model_Interfaces_OptionsByParams
 */
class Peppermint_FinancingOptions_Model_Interfaces_OptionsByParams extends Rockar_FinancingOptions_Model_Interfaces_OptionsByParams
{
    /**
     * @return string
     */
    public function getWebsiteCode()
    {
        return strtoupper(Mage::app()->getWebsite()->getCode());
    }
}

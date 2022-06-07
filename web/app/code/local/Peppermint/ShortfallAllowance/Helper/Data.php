<?php
/**
 * @category  Peppermint
 * @package   Peppermint_ShortfallAllowance
 * @author    Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

class Peppermint_ShortfallAllowance_Helper_Data extends Rockar_All_Helper_Data
{
    /**
     * Get model attribute.
     *
     * @return string
     */
    public function __construct()
    {
        $this->_modelAttribute = 'model_code';
    }
    
    /**
     * Get the short fall allowance limit after the products model_code
     *
     * @param $modelCode integer
     * @return float
     */
    public function getShortfallAllowanceAfterModelCode($modelCode)
    {
        $shortfallAllowance = Mage::getModel('peppermint_shortfallallowance/shortfall_allowance')->getCollection()
            ->addFieldToFilter('models', ['finset' => $modelCode])
            ->addFieldToSelect('shortfall_limit')
            ->getFirstItem()
            ->getData('shortfall_limit');

        return $shortfallAllowance ?? 0;
    }
}

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Transactional
 * @author    Donald Mailula <mailula.donald@partner.bmw.co.za>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Transactional_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @var array $cashMethods
     */
    private $_cashMethods = [
        "cashbmw",
        "cashmini",
        "motocash"
    ];

    /**
     * Is Enabled
     * ----------
     * Check to see if transaction module integration has been enabled.
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag('peppermint_transactional/general/enabled');
    }

    /**
     * Is Financed
     * ----------
     * Check to see if the sale is financed
     * @return boolean
    */
    public function isFinanced($financeOption)
    {
        return !in_array($financeOption, $this->_cashMethods);
    }
}

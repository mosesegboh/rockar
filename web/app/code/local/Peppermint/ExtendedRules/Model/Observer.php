<?php

/**
 * @category     Peppermint
 * @package      Peppermint_ExtendedRules
 * @author       Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_ExtendedRules_Model_Observer
{
    /*
     * 
     * @param Varien_Event_Observer $observer
     * @return Peppermint_ExtendedRules_Model_Observer
     */
    public function applyDeductions($observer)
    {
        Mage::helper('peppermint_extendedrules')->getProcessedDeductedValues($observer->getEvent()->getPartExchangeObject());

        return $this;
    }
}

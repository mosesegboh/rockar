<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Dfe
 * @author    Adrian Grigorita <adrian.grigorita@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Dfe_Model_Observer
{
    /**
     * Event triggered after amendment is completed.
     *
     * @param  Varien_Event_Observer $observer
     * @return $this
     */
    public function sendOrderAfterAmendmentToDfe(Varien_Event_Observer $observer)
    {
        return Mage::helper('peppermint_dfe/submitApp')->sendOrderToDfe($observer->getAmendmentOrder());
    }

    /**
     * Event triggered after checkout is completed.
     *
     * @param  Varien_Event_Observer $observer
     * @return $this
     */
    public function sendOrderAfterCheckoutToDfe(Varien_Event_Observer $observer)
    {
        return Mage::helper('peppermint_dfe/submitApp')->sendOrderToDfe($observer->getOrder());
    }
}

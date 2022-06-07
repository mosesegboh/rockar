<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Jiraphong Witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Sales_Model_Observer_Email
{
    /**
     * Observer Wrapper to add additional Email variables
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function addEmailVariables(Varien_Event_Observer $observer)
    {
        ['order' => $orderItem, 'template_variables' => $templateVars] = $observer->getData();

        if ($orderItem && $templateVars) {
            Mage::helper('peppermint_sales/EmailVariable')->addAdditionalEmailVariables($orderItem, $templateVars);
        }
    }
}

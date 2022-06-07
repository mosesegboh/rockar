<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Salesrule
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Salesrule_Model_Observer extends Mage_SalesRule_Model_Observer
{
    /**
     * Remove generate button if by acl
     *
     * @param $observer
     */
    public function updateCouponFormWithAcl($observer)
    {
        /** @var Varien_Data_Form $fieldset */
        if (!Mage::getSingleton('admin/session')->isAllowed('promo/quote/actions/coupons')) {
            $observer->getData('form')
                ->getElement('information_fieldset')
                ->removeField('generate_button');
        }
    }
}

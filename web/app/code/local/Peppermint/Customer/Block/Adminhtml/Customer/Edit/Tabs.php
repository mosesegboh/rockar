<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Customer
 * @author    Andrian Kogoshvili <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Customer_Block_Adminhtml_Customer_Edit_Tabs extends Mage_Adminhtml_Block_Customer_Edit_Tabs
{
    /**
     * Add tabs to customer page
     *
     * Overridden to add check to remove reviews tab if module is disabled
     *
     * @return Peppermint_Customer_Block_Adminhtml_Customer_Edit_Tabs;
     */
    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        if (Mage::registry('current_customer')->getId()
            && !Mage::helper('catalog')->isModuleEnabled('Mage_Review')) {
            $this->removeTab('reviews');
        }

        return Mage_Adminhtml_Block_Widget_Tabs::_beforeToHtml();
    }
}

<?php

/**
 * @category     Peppermint
 * @package      Peppermint_FutureValue
 * @author       Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_FutureValue_Block_Adminhtml_Rules_Edit_Tabs extends Rockar_FutureValue_Block_Adminhtml_Rules_Edit_Tabs
{
    /**
     * Update/add specific tabs
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();
        $mileageTabContent = $this->getLayout()
            ->createBlock('peppermint_extendedrules/adminhtml_rules_edit_tab_mileage')
            ->toHtml();
        $this->addTabAfter(
            'mileage_section',
            [
                'label' => $this->__('Mileage'),
                'title' => $this->__('Mileage'),
                'content' => $mileageTabContent
            ],
            'part_exchange_future_section'
        );
        $mmCodeLabel = $this->__('Exception By MM Code');
        $this->setTabData('exception_cap_section', 'label', $mmCodeLabel)
            ->setTabData('exception_cap_section', 'title', $mmCodeLabel);
        $this->removeTab('colour_section');

        return Mage_Adminhtml_Block_Widget_Tabs::_beforeToHtml();
    }
}

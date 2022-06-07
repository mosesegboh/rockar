<?php

/**
 * @category     Peppermint
 * @package      Peppermint\ExtendedRules
 * @author       Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_ExtendedRules_Block_Adminhtml_Rules_Mileage_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected $_activeTab = 'mileage_section';

    public function __construct()
    {
        $this->_blockGroup = 'peppermint_extendedrules';
        $this->_controller = 'adminhtml_rules_mileage';

        parent::__construct();

        $this->extendedRulesHelper = Mage::helper('rockar_extendedrules');
        $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->_getBackUrl() . '\')')
            ->_updateButton('save', 'onclick', 'submitEditForm($(\'edit_form\').action)');
        $this->_addButton('saveandcontinue', [
            'label' =>  $this->extendedRulesHelper->__('Save and Continue Edit'),
            'onclick' => 'submitEditForm($(\'edit_form\').action + \'back/edit/\')',
            'class' => 'save',
        ], -100);
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return string
     */
    public function getHeaderText()
    {
        return ($id = Mage::registry('current_rule')->getId()) ?
            $this->extendedRulesHelper->__("Edit Mileage Rule. (ID: %s)", $id) : 
            $this->extendedRulesHelper->__('New Mileage Rule');
    }

    /**
     * Getter of url for "Back" button
     *
     * @return string
     */
    protected function _getBackUrl()
    {
        return $this->getUrl('*/extendedrules_rules/index', ['active_tab' => $this->_activeTab]);
    }
}

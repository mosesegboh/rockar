<?php

/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Lucaci Stefan <lucacistefan.alexandru@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

/**
 * Class Peppermint_FinancingOptions_Block_Adminhtml_Group_Edit_Tab_Filter
 */
class Peppermint_FinancingOptions_Block_Adminhtml_Group_Edit_Tab_Filter extends Rockar_FinancingOptions_Block_Adminhtml_Group_Edit_Tab_Filter
{
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $this->_configHelper = Mage::helper('peppermint_financingoptions/config');
        $this->_addBalloonFields();

        return $this->_prepareFormAfter();
    }

    /**
     * Add balloon fields to the form
     *
     * @return $this
     */
    protected function _addBalloonFields()
    {
        $balloonFieldset = $this->_form->addFieldset('balloon_fieldset', [
            'legend' => $this->_helper->__('Balloon Slider'),
            'class' => 'fieldset-wide',
            'disabled' => false
        ]);
        $balloonFieldset->addField('balloon_slider_steps', 'text', [
            'name' => 'balloon_slider_steps',
            'label' => $this->_helper->__('Slider Steps'),
            'note' => $this->_configHelper->getBalloonStepsConfig()
        ]);
        $balloonFieldset->addField('balloon_default_step', 'text', [
            'name' => 'balloon_default_step',
            'label' => $this->_helper->__('Default Steps'),
            'note' => $this->_configHelper->getBalloonDefaultConfig()
        ]);

        return $this;
    }
}

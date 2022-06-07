<?php
/**
 * @category  Peppermint
 * @package   Peppermint_ShortfallAllowance
 * @author    Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

class Peppermint_ShortfallAllowance_Block_Adminhtml_Shortfall_Allowance_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_shortfall_allowance';
        $this->_blockGroup = 'peppermint_shortfallallowance';

        parent::__construct();

        $this->_addButton(
            'saveandcontinue',
            [
                'label' => Mage::helper('adminhtml')->__('Save and Continue Edit'),
                'onclick' => 'saveAndContinueEdit(\'' . $this->_getSaveAndContinueUrl() . '\')',
                'class' => 'save'
            ], 
            -100
        );
        $this->_formScripts[]
            = "function saveAndContinueEdit(urlTemplate) {
                    var template = new Template(urlTemplate, /(^|.|\\r|\\n)({{(\w+)}})/),
                        url = template.evaluate({});
                    editForm.submit(url);
                }";
    }

    /**
     * getHeaderText.
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('peppermint_shortfall_allowance')->getId()) {
            return $this->__('Edit');
        }

        return $this->__('Create New');
    }

    /**
     * Getter of url for "Save and Continue" button.
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl(
            '*/*/save',
            [
                '_current' => true,
                'back' => 'edit'
            ]
        );
    }
}

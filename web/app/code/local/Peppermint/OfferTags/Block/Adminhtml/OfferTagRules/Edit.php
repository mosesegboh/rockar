<?php
/**
 * @category  Peppermint
 * @package   Peppermint_OfferTags
 * @author    Ketevani Revazishvili<techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_OfferTags_Block_Adminhtml_OfferTagRules_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_objectId = 'rule_id';
        $this->_blockGroup = 'peppermint_offertags';
        $this->_controller = 'adminhtml_offerTagRules';

        parent::__construct();

        $this->_addButton('save_and_continue_edit', [
            'class' => 'save',
            'label' => $this->__('Save and Continue Edit'),
            'onclick' => 'editForm.submit($(\'edit_form\').action + \'back/edit/\')',
            ], 10
        );
    }

    /**
     * getHeaderText.
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('peppermint_offertags')->getId()) {
            return $this->__('Edit Rule');
        }

        return $this->__('Create New Rule');
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

    /**
     * Get form save URL
     *
     * @deprecated
     * @see getFormActionUrl()
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save');
    }
}

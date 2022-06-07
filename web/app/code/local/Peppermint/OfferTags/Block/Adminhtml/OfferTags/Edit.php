<?php
/**
 * @category  Peppermint
 * @package   Peppermint_OfferTags
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_OfferTags_Block_Adminhtml_OfferTags_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_objectId = 'offertag_id';
        $this->_blockGroup = Peppermint_OfferTags_Block_Adminhtml_OfferTags::OFFERTAGS_BLOCK_GROUP;
        $this->_controller = 'adminhtml_offerTags';

        parent::__construct();

        $this->_addButton(
            'saveandcontinue',
            [
                'label' => $this->__('Save and Continue Edit'),
                'onclick' => 'saveAndContinueEdit(\'' . $this->_getSaveAndContinueUrl() . '\')',
                'class' => 'save'
            ],
            -100
        );
        $this->_formScripts[]
            = 'function saveAndContinueEdit(urlTemplate) {
                    var template = new Template(urlTemplate, /(^|.|\\r|\\n)({{(\\w+)}})/),
                        url = template.evaluate({});
                    editForm.submit(url);
                }';
    }

    /**
     * getHeaderText.
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('peppermint_offertags')->getId()) {
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

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_OfferTags
 * @author    Taras Kapushchak <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Offertags_Block_Adminhtml_OfferTagRules_Edit_Tab_Actions
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Prepare content for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Actions');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Actions');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Add needed fields to the form
     *
     * @return Peppermint_Offertags_Block_Adminhtml_OfferTagRules_Edit_Tab_Actions
     */
    protected function _prepareForm()
    {
        $model = Mage::registry('peppermint_offertags');
        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('actions_fieldset', ['legend' => $this->__('Offer Tags Actions')]);

        $offerTagsArray = Mage::helper('peppermint_offertags')->getOfferTagsArray(true);

        $fieldset->addField(
            'offer_tag_id',
            'select',
            [
                'name' => 'offer_tag_id',
                'label' => $this->__('Offer Tag Item'),
                'title' => $this->__('Offer Tag Item'),
                'required' => true,
                'options' => $offerTagsArray
            ]
        );

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Experiences
 * @author    Jvegenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Experiences_Block_Adminhtml_ExperiencesRules_Edit_Tab_Actions
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
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Add needed fields to the form
     *
     * @return Peppermint_Experiences_Block_Adminhtml_ExperiencesRules_Edit_Tab_Actions
     */
    protected function _prepareForm()
    {
        $model = Mage::registry('current_experiences_rule');
        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('actions_fieldset', ['legend' => $this->__('Experiences Actions')]);

        $experiencesArray = Mage::helper('peppermint_experiences')->getExperiencesArray();

        $fieldset->addField(
            'experience_id',
            'select',
            [
                'name' => 'experience_id',
                'label' => $this->__('Experience Item'),
                'title' => $this->__('Experience Item'),
                'required' => true,
                'options' => $experiencesArray
            ]
        );

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}

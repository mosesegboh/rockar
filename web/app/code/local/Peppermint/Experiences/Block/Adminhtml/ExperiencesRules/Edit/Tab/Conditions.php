<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Experiences
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Experiences_Block_Adminhtml_ExperiencesRules_Edit_Tab_Conditions
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
        return $this->__('Conditions');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Conditions');
    }

    /**
     * Returns status flag about this tab can be showen or not
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
     * {@inheritDoc}
     */
    protected function _prepareForm()
    {
        $model = Mage::registry('current_experiences_rule');

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('rule_');

        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('promo/fieldset.phtml')
            ->setNewChildUrl($this->getUrl('*/*/newConditionHtml/form/rule_conditions_fieldset'));

        $fieldset = $form->addFieldset('conditions_fieldset',
            ['legend' => $this->__('Experiences Conditions')]
        )->setRenderer($renderer);

        $fieldset->addField('conditions', 'text',
            [
                'name' => 'conditions',
                'required' => true,
                'label' => $this->__('Experiences Conditions'),
                'title' => $this->__('Experiences Conditions')
            ])->setRule($model)->setRenderer(Mage::getBlockSingleton('rule/conditions'));

        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }
}

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_OfferTags
 * @author    Ketevani Revazishvili<techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_OfferTags_Block_Adminhtml_OfferTagRules_Edit_Tab_Main
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
        return $this->__('Rule Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Rule Information');
    }

    /**
     * Returns status flag about this tab can be showed or not
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
     * Prepare form before rendering HTML
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = Mage::registry('peppermint_offertags');

        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset(
            'general_fieldset',
            ['legend' => $this->__('General Information')]
        );

        if ($model->getId()) {
            $fieldset->addField('rule_id', 'hidden', ['name' => 'rule_id']);
        }

        $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => $this->__('Rule Name'),
                'title' => $this->__('Rule Name'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'description',
            'textarea',
            [
                'name' => 'description',
                'label' => $this->__('Description'),
                'title' => $this->__('Description'),
                'required' => false,
                'style' => 'height: 100px;'
            ]
        );

        $fieldset->addField(
            'is_active',
            'select',
            [
                'name' => 'is_active',
                'label' => $this->__('Status'),
                'title' => $this->__('Status'),
                'required' => true,
                'options' => [1 => $this->__('Enabled'), 0 => $this->__('Disabled')]
            ]
        );

        if (Mage::app()->isSingleStoreMode()) {
            $websiteId = Mage::app()->getStore(true)->getWebsiteId();
            $fieldset->addField('website_ids', 'hidden', [
                'name'     => 'website_ids[]',
                'value'    => $websiteId
            ]);
            $model->setWebsiteIds($websiteId);
        } else {
            $field = $fieldset->addField('website_ids', 'multiselect', [
                'name'     => 'website_ids[]',
                'label'     => $this->__('Websites'),
                'title'     => $this->__('Websites'),
                'required' => true,
                'values'   =>  Mage::getSingleton('peppermint_catalogrule/system_config_source_websiteIds')->toOptionArray()
            ]);
            $field->setRenderer($this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element'));
        }

        $fieldset->addField('customer_group_ids', 'multiselect', [
            'name' => 'customer_group_ids[]',
            'label' => $this->__('Customer Groups'),
            'title' => $this->__('Customer Groups'),
            'required' => true,
            'values' => Mage::getResourceModel('customer/group_collection')->toOptionArray()
        ]);

        $groupsHash = Mage::getResourceModel('rockar_financingoptions/group_collection')
            ->load()
            ->toOptionHash();
        $groups = Mage::helper('peppermint_offertags/data')->toOptionArray($groupsHash);

        $fieldset->addField('finance_group_ids', 'multiselect', [
            'name' => 'finance_group_ids[]',
            'label' => $this->__('Finance Groups'),
            'title' => $this->__('Finance Groups'),
            'required' => true,
            'values' => $groups
        ]);

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

        $fieldset->addField(
            'from_date',
            'date',
            [
                'name' => 'from_date',
                'label' => $this->__('From Date'),
                'title' => $this->__('From Date'),
                'required' => false,
                'image' => $this->getSkinUrl('images/grid-cal.gif'),
                'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
                'format' => $dateFormatIso
            ]
        );

        $fieldset->addField(
            'to_date',
            'date',
            [
                'name' => 'to_date',
                'label' => $this->__('To Date'),
                'title' => $this->__('To Date'),
                'required' => false,
                'image' => $this->getSkinUrl('images/grid-cal.gif'),
                'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
                'format' => $dateFormatIso
            ]
        );

        $fieldset->addField(
            'priority',
            'text',
            [
                'name' => 'priority',
                'label' => $this->__('Priority'),
                'title' => $this->__('Priority'),
                'required' => false,
                'class' => 'validate-not-negative-number'
            ]
        );

        $form->setValues($model->getData());
        $this->setForm($form);

        return $this;
    }
}

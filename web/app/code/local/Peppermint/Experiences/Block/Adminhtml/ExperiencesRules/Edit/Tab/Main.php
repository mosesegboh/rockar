<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Experiences
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Experiences_Block_Adminhtml_ExperiencesRules_Edit_Tab_Main
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
     * Prepare form before rendering HTML
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = Mage::registry('current_experiences_rule');

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

        $couponTypeField = $fieldset->addField('coupon_type', 'select', [
            'name'       => 'coupon_type',
            'label'      => $this->__('Coupon'),
            'required'   => true,
            'options'    => Mage::getModel('peppermint_experiences/experiencesRules')->getCouponTypes()
        ]);

        $couponCodeField = $fieldset->addField('coupon_code', 'text', [
            'name' => 'coupon_code',
            'label' => $this->__('Coupon Code'),
            'required' => true
        ]);

        $autoGenerationCheckbox = $fieldset->addField('use_auto_generation', 'checkbox', [
            'name'  => 'use_auto_generation',
            'label' => $this->__('Use Auto Generation'),
            'note'  => $this->__('If you select and save the rule you will be able to generate multiple coupon codes.'),
            'onclick' => 'handleCouponsTabContentActivity()',
            'checked' => $model->getUseAutoGeneration() ? 'checked' : ''
        ]);

        $autoGenerationCheckbox->setRenderer(
            $this->getLayout()->createBlock('adminhtml/promo_quote_edit_tab_main_renderer_checkbox')
        );

        $usesPerCouponField = $fieldset->addField('uses_per_coupon', 'text', [
            'name' => 'uses_per_coupon',
            'label' => $this->__('Uses per Coupon')
        ]);

        $fieldset->addField('uses_per_customer', 'text', [
            'name' => 'uses_per_customer',
            'label' => $this->__('Uses per Customer'),
            'note' => $this->__('Usage limit enforced for logged in customers only')
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

        // field dependencies
        $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
            ->addFieldMap($couponTypeField->getHtmlId(), $couponTypeField->getName())
            ->addFieldMap($couponCodeField->getHtmlId(), $couponCodeField->getName())
            ->addFieldMap($autoGenerationCheckbox->getHtmlId(), $autoGenerationCheckbox->getName())
            ->addFieldMap($usesPerCouponField->getHtmlId(), $usesPerCouponField->getName())
            ->addFieldDependence(
                $couponCodeField->getName(),
                $couponTypeField->getName(),
                Peppermint_Experiences_Model_ExperiencesRules::COUPON_TYPE_SPECIFIC)
            ->addFieldDependence(
                $autoGenerationCheckbox->getName(),
                $couponTypeField->getName(),
                Peppermint_Experiences_Model_ExperiencesRules::COUPON_TYPE_SPECIFIC)
            ->addFieldDependence(
                $usesPerCouponField->getName(),
                $couponTypeField->getName(),
                Peppermint_Experiences_Model_ExperiencesRules::COUPON_TYPE_SPECIFIC)
        );

        return $this;
    }
}

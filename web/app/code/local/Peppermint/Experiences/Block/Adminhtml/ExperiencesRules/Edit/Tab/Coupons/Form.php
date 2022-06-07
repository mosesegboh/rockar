<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Experiences
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Experiences_Block_Adminhtml_ExperiencesRules_Edit_Tab_Coupons_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare coupon codes generation parameters form
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        /**
         * @var Peppermint_Experiences_Helper_Coupon $couponHelper
         */
        $couponHelper = Mage::helper('peppermint_experiences/coupon');

        $model = Mage::registry('current_experiences_rule');
        $ruleId = $model->getId();

        $form->setHtmlIdPrefix('coupons_');

        $gridBlock = $this->getLayout()->getBlock('experiencesRules_edit_tab_coupons_grid');
        $gridBlockJsObject = '';

        if ($gridBlock) {
            $gridBlockJsObject = $gridBlock->getJsObjectName();
        }

        $fieldset = $form->addFieldset('information_fieldset', ['legend'=>$this->__('Coupons Information')]);
        $fieldset->addClass('ignore-validate');

        $fieldset->addField('rule_id', 'hidden', [
            'name'     => 'rule_id',
            'value'    => $ruleId
        ]);

        $fieldset->addField('qty', 'text', [
            'name'     => 'qty',
            'label'    => $this->__('Coupon Qty'),
            'title'    => $this->__('Coupon Qty'),
            'required' => true,
            'class'    => 'validate-digits validate-greater-than-zero'
        ]);

        $fieldset->addField('length', 'text', [
            'name'     => 'length',
            'label'    => $this->__('Code Length'),
            'title'    => $this->__('Code Length'),
            'required' => true,
            'note'     => $this->__('Excluding prefix, suffix and separators.'),
            'value'    => $couponHelper->getDefaultLength(),
            'class'    => 'validate-digits validate-greater-than-zero'
        ]);

        $fieldset->addField('format', 'select', [
            'label'    => $this->__('Code Format'),
            'name'     => 'format',
            'options'  => $couponHelper->getFormatsList(),
            'required' => true,
            'value'    => $couponHelper->getDefaultFormat()
        ]);

        $fieldset->addField('prefix', 'text', [
            'name'  => 'prefix',
            'label' => $this->__('Code Prefix'),
            'title' => $this->__('Code Prefix'),
            'value' => $couponHelper->getDefaultPrefix()
        ]);

        $fieldset->addField('suffix', 'text', [
            'name'  => 'suffix',
            'label' => $this->__('Code Suffix'),
            'title' => $this->__('Code Suffix'),
            'value' => $couponHelper->getDefaultSuffix()
        ]);

        $fieldset->addField('dash', 'text', [
            'name'  => 'dash',
            'label' => $this->__('Dash Every X Characters'),
            'title' => $this->__('Dash Every X Characters'),
            'note'  => $this->__('If empty no separation.'),
            'value' => $couponHelper->getDefaultDashInterval(),
            'class' => 'validate-digits'
        ]);

        $idPrefix = $form->getHtmlIdPrefix();
        $generateUrl = $this->getGenerateUrl();

        $fieldset->addField('generate_button', 'note', [
            'text' => $this->getButtonHtml(
                $this->__('Generate'),
                "generateCouponCodes('{$idPrefix}' ,'{$generateUrl}', '{$gridBlockJsObject}')",
                'generate'
            )
        ]);

        $this->setForm($form);

        Mage::dispatchEvent('peppermint_experiences_adminhtml_experiencesrules_edit_tab_coupons_form_prepare_form', ['form' => $form]);

        return parent::_prepareForm();
    }

    /**
     * Retrieve URL to Generate Action
     *
     * @return string
     */
    public function getGenerateUrl()
    {
        return $this->getUrl('*/*/generate');
    }
}

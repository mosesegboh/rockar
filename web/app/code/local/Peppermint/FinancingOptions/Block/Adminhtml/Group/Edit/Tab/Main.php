<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

class Peppermint_FinancingOptions_Block_Adminhtml_Group_Edit_Tab_Main extends Rockar_FinancingOptions_Block_Adminhtml_Group_Edit_Tab_Main
{
    
    /**
     * stores the website ids website names array.
     *
     * @var array
     */
    private $_websiteOptionArr;

    /**
     * Gets the website ids website names array.
     *
     * @return array
     */
    protected function _getWebsiteOptionsArray()
    {
        if (!$this->_websiteOptionArr) {
            foreach (Mage::app()->getWebsites() as $key => $value) {
                $this->_websiteOptionArr[$key] = $value->getCode();
            }
        }

        return $this->_websiteOptionArr;
    }

    /**
     * Add 2 new fields.
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();

        $this->_fieldset->addField(
            'website',
            'select',
            [
                'name' => 'website',
                'label' => $this->_helper->__('Website'),
                'title' => $this->_helper->__('Website'),
                'values' => $this->_getWebsiteOptionsArray(),
                'required' => true
            ]
        );

        $this->_fieldset->addField(
            'method_type',
            'select',
            [
                'name' => 'method_type',
                'label' => $this->_helper->__('Method Type'),
                'title' => $this->_helper->__('Method Type'),
                'values' => Mage::getSingleton('peppermint_financingoptions/adminhtml_system_config_source_methodType')->toArray(),
                'required' => true
            ]
        );

        return $this->_prepareFormAfter();
    }
}

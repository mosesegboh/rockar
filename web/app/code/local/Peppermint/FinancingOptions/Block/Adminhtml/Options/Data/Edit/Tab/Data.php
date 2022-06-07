<?php

/**
 * @category     Peppermint
 * @package      Peppermint\FinancingOptions
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

/**
 * Class Peppermint_FinancingOptions_Block_Adminhtml_Options_Data_Edit_Tab_Data
 */
class Peppermint_FinancingOptions_Block_Adminhtml_Options_Data_Edit_Tab_Data extends Rockar_FinancingOptions_Block_Adminhtml_Options_Data_Edit_Tab_Data
{
    /**
     * Removes existing field (capid) and creates it again with a different label
     *
     * @see Rockar_FinancingOptions_Block_Adminhtml_Options_Data_Edit_Tab_Data::_initGeneralFields()
     * @return Peppermint_FinancingOptions_Block_Adminhtml_Options_Data_Edit_Tab_Data
     */
    protected function _initGeneralFields()
    {
        parent::_initGeneralFields();
        $this->_fieldset->getElements()
            ->searchById('capid')
            ->setTitle($this->__('Asset ID'))
            ->setLabel($this->__('Asset ID'));

        return $this;
    }

    /**
     * Prepare Leasing fields
     *
     * @return Peppermint_FinancingOptions_Block_Adminhtml_Options_Data_Edit_Tab_Data
     */
    protected function _initLeasingFields()
    {
        $rate_subvention_types = Mage::getStoreConfig('system/peppermint_financingoptions/rate_subvention_types');

        $fields = [
            'manufacture_deposit' => [
                'type' => 'text',
                'title' => 'Manufacture Deposit',
                'required' => true,
                'note' => null,
            ],
            'dealer_deposit' => [
                'type' => 'text',
                'title' => 'Dealer Deposit',
                'required' => true,
                'note' => null,
            ],
            'optional_final_payment' => [
                'type' => 'text',
                'title' => 'Optional Final Payment',
                'required' => true,
                'note' => null,
                'space' => true,
            ],
            'rate_subvention_value' => [
                'type' => 'text',
                'title' => 'Rate Subvention Value',
                'required' => false,
                'note' => null,
            ],
            'rate_subvention_type' => [
                'type' => 'select',
                'values' => [
                    [
                        'value' => null,
                        'label' => ''
                    ],
                    [
                        'value' => $rate_subvention_types['type_flatamount'],
                        'label' => 'FlatAmount'
                    ],
                    [
                        'value' => $rate_subvention_types['type_percentage'],
                        'label' => 'Percentage'
                    ],
                ],
                'title' => 'Rate Subvention Type',
                'required' => false,
                'note' => null,
            ],
            'max_balloon_percent' => [
                'type' => 'text',
                'title' => 'Maximum Balloon Percentage',
                'required' => false,
                'note' => null
            ],
            'vehicle_type' => [
                'type' => 'text',
                'title' => 'Vehicle Type',
                'required' => false,
                'note' => null
            ]
        ];

        return $this->_createFields($fields);
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $this->_prepareFormBefore();
        $productId = (int) $this->_model->getData('product_id');

        if ($productId && $this->_model->getId() && $this->_countDataAssignedProducts() > 1) {
            $this->_fieldset->addField(
                'data_multiple_product_note',
                'note',
                [
                    'text' => '<span class="error">'
                        . $this->_helper->__('Current Finance Data Item is assigned to the multiple products.<br>Please be carefully with editing!!!<br><br>If you want do changes only for current product please create new Finance Data.')
                        . '</span>',
                ]
            );
        }

        if ($this->_model->getId()) {
            $this->_fieldset->addField(
                'data_id',
                'hidden',
                [
                    'name' => 'data_id',
                ]
            );
        }

        if ($this->_model->getData('options_id')) {
            $this->_fieldset->addField(
                'options_id',
                'hidden',
                [
                    'name' => 'options_id',
                ]
            );
        }

        if ($productId) {
            $this->_fieldset->addField(
                'product_id',
                'hidden',
                [
                    'name' => 'product_id',
                ]
            );
        }

        if ($this->_model->getData('active_tab')) {
            $this->_fieldset->addField(
                'active_tab',
                'hidden',
                [
                    'name' => 'active_tab',
                ]
            );
        }

        /**
         * @var Rockar_FinancingOptions_Model_Options $option
         */
        $option = (Mage::registry('financing_option')) ? Mage::registry('financing_option') : new Varien_Object();

        $this->_initGeneralFields();
        if (!$option->isHirePayment()) {
            $this->_initLeasingFields();
        }

        return $this->_prepareFormAfter();
    }
}

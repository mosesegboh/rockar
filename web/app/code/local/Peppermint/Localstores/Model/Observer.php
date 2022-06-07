<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Localstores
 * @author    Catalin Lungu <catalin.lungu@rockar.com>, Ana-Maria Buliga <anamara.buliga@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Localstores_Model_Observer
{
    /**
     * Extending the addresses tab edit form by adding new fields.
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function prepareAddressTabForm(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('rockar_localstores');

        /** @var Rockar_Localstores_Block_Adminhtml_Localstores_Edit_Tab_Address $fieldset */
        $fieldset = $observer->getData('fieldset');

        $fieldset->addField(
            'legal_entity',
            'text',
            [
                'name' => 'legal_entity',
                'label' => $helper->__('Legal Trading Entity'),
                'title' => $helper->__('Legal Trading Entity')
            ]
        );

        $fieldset->addField(
            'registration_number',
            'text',
            [
                'name' => 'registration_number',
                'label' => $helper->__('Registration Number'),
                'title' => $helper->__('Registration Number')
            ]
        );

        $fieldset->addField(
            'vat_number',
            'text',
            [
                'name' => 'vat_number',
                'label' => $helper->__('VAT Number'),
                'title' => $helper->__('VAT Number')
            ]
        );

        $fieldset->addField(
            'email_address',
            'text',
            [
                'name' => 'email_address',
                'label' => $helper->__('Dealer Email Address'),
                'title' => $helper->__('Dealer Email Address')
            ]
        );

        $fieldset->addField(
            'postal_address_line_1',
            'text',
            [
                'name' => 'postal_address_line_1',
                'label' => $helper->__('Postal address line 1'),
                'title' => $helper->__('Postal address line 1')
            ],
            'longitude'
        );

        $fieldset->addField(
            'postal_address_line_2',
            'text',
            [
                'name' => 'postal_address_line_2',
                'label' => $helper->__('Postal address line 2'),
                'title' => $helper->__('Postal address line 2')
            ],
            'postal_address_line_1'
        );

        $fieldset->addField(
            'postal_address_line_3',
            'text',
            [
                'name' => 'postal_address_line_3',
                'label' => $helper->__('Postal address line 3'),
                'title' => $helper->__('Postal address line 3')
            ],
            'postal_address_line_2'
        );

        $fieldset->addField(
            'postal_address_city',
            'text',
            [
                'name' => 'postal_address_city',
                'label' => $helper->__('Postal address city'),
                'title' => $helper->__('Postal address city')
            ],
            'postal_address_line_3'
        );

        $fieldset->addField(
            'postal_address_postal_code',
            'text',
            [
                'name' => 'postal_address_postal_code',
                'label' => $helper->__('Postal address postal code'),
                'title' => $helper->__('Postal address postal code')
            ],
            'postal_address_city'
        );

        $fieldset->addField(
            'postal_address_province',
            'text',
            [
                'name' => 'postal_address_province',
                'label' => $helper->__('Postal address province'),
                'title' => $helper->__('Postal address province')
            ],
            'postal_address_postal_code'
        );

        $fieldset->addField(
            'province_code',
            'text',
            [
                'name' => 'province_code',
                'label' => $helper->__('Province code'),
                'title' => $helper->__('Province code')
            ],
            'email_address'
        );

        $fieldset->addField(
            'province_name',
            'text',
            [
                'name' => 'province_name',
                'label' => $helper->__('Province name'),
                'title' => $helper->__('Province name')
            ],
            'province_code'
        );

        return $this;
    }

    /**
     * Extending the general tab edit form by adding new fields.
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function prepareGeneralTabForm(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('rockar_localstores');
        $fieldset = $observer->getData('fieldset');

        $fieldset->removeField('enable_youdrive');

        $fieldset->addField(
            'enable_youdrive',
            'select',
            [
                'name' => 'enable_youdrive',
                'label' => $helper->__('Enable for Test Drive Booking'),
                'title' => $helper->__('Enable for Test Drive Booking'),
                'values' => $helper->getStatusValuesArray()
            ],
            'status'
        );

        $fieldset->addField(
            'enable_youdrive_request',
            'select',
            [
                'name' => 'enable_youdrive_request',
                'label' => $helper->__('Enable for Test Drive Request'),
                'title' => $helper->__('Enable for Test Drive Request'),
                'values' => $helper->getStatusValuesArray()

            ],
            'enable_youdrive'
        );

        $fieldset->addField(
            'dealer_code',
            'text',
            [
                'name' => 'dealer_code',
                'label' => $helper->__('Dealer Code'),
                'title' => $helper->__('Dealer Code')
            ],
            'name'
        );

        $fieldset->addField(
            'brand_code',
            'text',
            [
                'name' => 'brand_code',
                'label' => $helper->__('Brand Code'),
                'title' => $helper->__('Brand Code')
            ],
            'dealer_code'
        );

        $fieldset->addField(
            'financial_services_provider_number',
            'text',
            [
                'name' => 'financial_services_provider_number',
                'label' => $helper->__('Financial Services Provider Number'),
                'title' => $helper->__('Financial Services Provider Number')
            ],
            'brand_code'
        );

        $fieldset->addField(
            'associated_compound_dealer',
            'text',
            [
                'name' => 'associated_compound_dealer',
                'label' => $helper->__('Associated Compound Store'),
                'title' => $helper->__('Associated Compound Store'),
            ],
            'financial_services_provider_number'
        );

        $fieldset->addField(
            'is_compound_dealer',
            'select',
            [
                'name' => 'is_compound_dealer',
                'label' => $helper->__('Compound Dealer'),
                'title' => $helper->__('Compound Dealer'),
                'values' => Mage::helper('peppermint_localstores')->getSimpleValueArray()
            ],
            'associated_compound_store'
        );

        $fieldset->addField(
            'registered_company_name',
            'text',
            [
                'name' => 'registered_company_name',
                'label' => $helper->__('Registered Company Name'),
                'title' => $helper->__('Registered Company Name')
            ],
            'is_compound_dealer'
        );

        $fieldset->addField(
            'order_cap',
            'text',
            [
                'name' => 'order_cap',
                'label' => $helper->__('Order Cap'),
                'title' => $helper->__('Order Cap')
            ],
            'registered_company_name'
        );

        return $this;
    }

    /**
     * Set dealer ID for the order
     *
     * @param Varien_Event_Observer $observer
     */
    public function setDealerId(Varien_Event_Observer $observer)
    {
        if ($dealerId = Mage::getModel('core/cookie')->get(Peppermint_Customer_Model_Observer::DEALER_ID_COOKIE_NAME)) {
            $order = $observer->getEvent()->getOrder();
            $order->setDealerId($dealerId);
        }
    }

    /**
     * Add dealer id to the order grid
     *
     * @param Varien_Event_Observer $observer
     */
    public function addDealerIdToOrdersGrid(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();

        $block->addColumnAfter('dealer_id', [
            'header' => Mage::helper('customer')->__('UserID'),
            'index' => 'dealer_id',
            'type' => 'string'
        ], 'local_store_code');
    }
}

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Admin
 * @author    Ana-Maria Buliga <anamaria.buliga@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Admin_Block_Adminhtml_Role_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare form before rendering HTML.
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $adminHelper = Mage::helper('peppermint_admin');
        $adminSession = Mage::getSingleton('adminhtml/session');
        $currentRole = Mage::registry('current_role');
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('role_');
        $form->setFieldNameSuffix('role');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'role_form',
            ['legend' => $adminHelper->__('Role Information')]
        );

        $fieldset->addField(
            'role',
            'text',
            [
                'label' => $adminHelper->__('Role'),
                'name' => 'role',
                'required' => true,
                'class' => 'required-entry'
            ]
        );

        $fieldset->addField(
            'client_id',
            'text',
            [
                'label' => $adminHelper->__('Client ID'),
                'name' => 'client_id',
                'required' => true,
                'class' => 'required-entry'
            ]
        );

        $fieldset->addField(
            'client_secret',
            'text',
            [
                'label' => $adminHelper->__('Client Secret'),
                'name' => 'client_secret',
                'required' => true,
                'class' => 'required-entry'
            ]
        );

        $fieldset->addField(
            'realm',
            'text',
            [
                'label' => $adminHelper->__('Realm Path'),
                'name' => 'realm',
                'required' => true,
                'class' => 'required-entry'
            ]
        );

        $fieldset->addField(
            'status',
            'select',
            [
                'label' => $adminHelper->__('Status'),
                'name' => 'status',
                'values' => [
                    [
                        'value' => 1,
                        'label' => $adminHelper->__('Enabled')
                    ],
                    [
                        'value' => 0,
                        'label' => $adminHelper->__('Disabled')
                    ]
                ]
            ]
        );

        $formValues = $currentRole->getDefaultValues();

        if (!is_array($formValues)) {
            $formValues = [];
        }

        if ($adminSession->getRoleData()) {
            $formValues = array_merge($formValues, $adminSession->getRoleData());
            $adminSession->setRoleData(null);
        } else {
            if ($currentRole) {
                $formValues = array_merge($formValues, $currentRole->getData());
            }
        }

        $form->setValues($formValues);

        return parent::_prepareForm();
    }
}

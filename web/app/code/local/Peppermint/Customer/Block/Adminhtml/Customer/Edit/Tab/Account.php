<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Customer
 * @author    Krists Dadzitis <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Customer_Block_Adminhtml_Customer_Edit_Tab_Account extends Rockar_Customer_Block_Adminhtml_Customer_Edit_Tab_Account
{
    /**
     * Rewrite of parent function to add Created In (Local Store) field
     *
     * {@inheritDoc}
     */
    public function initForm()
    {
        parent::initForm();

        $form = $this->getForm();
        $form->getElement('base_fieldset')
            ->addField(
                'in_store_name',
                'text',
                [
                    'label' => $this->__('Created In (Local Store)'),
                    'name' => 'in_store_name',
                    'disabled' => true
                ]
            );

        $form->setValues(Mage::registry('current_customer')->getData());
        $this->setForm($form);

        return $this;
    }
}
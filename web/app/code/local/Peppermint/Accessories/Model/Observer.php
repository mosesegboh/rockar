<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Accessories
 * @author    Krists Dadzitis <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Accessories_Model_Observer
{
    /**
     * Observer function to update the Accessory edit form
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function prepareAccessoryForm($observer)
    {
        $helper = Mage::helper('rockar_all');
        $fieldset = $observer->getFieldset();

        if ($fieldset) {
            $fieldset->removeField('identifier')
                ->removeField('image')
                ->removeField('custom_image');

            $fieldset->addField('identifier', 'text', [
                'name' => 'identifier',
                'label' => $helper->__('Identifier'),
                'title' => $helper->__('Identifier'),
                'required' => true,
                'note' => $helper->__('Must Be Unique Identifier')
            ], '^');

            $fieldset->addField('material_number', 'text', [
                'name' => 'material_number',
                'label' => $helper->__('Material Number'),
                'title' => $helper->__('Material Number'),
                'required' => true,
            ], 'identifier');

            $fieldset->addField('image', 'text', [
                'name' => 'image',
                'label' => $helper->__('Image Url'),
                'title' => $helper->__('Image'),
                'required' => false
            ], 'custom_description');

            $fieldset->addField('option_code', 'text', [
                'name' => 'option_code',
                'label' => $helper->__('Indicator'),
                'title' => $helper->__('Indicator'),
                'required' => true
            ]);
        }
    }
}
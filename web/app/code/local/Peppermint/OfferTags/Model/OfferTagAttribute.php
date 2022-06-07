<?php
/**
 * @category  Peppermint
 * @package   Peppermint_OfferTags
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_OfferTags_Model_OfferTagAttribute extends Varien_Object
{
    /**
     * Peppermint_OfferTags_Model_OfferTagAttribute constructor.
     */
    public function __construct()
    {
        $offerTagsHelper = Mage::helper('peppermint_offertags');

        $data = [
                'attribute_code' => 'offer_tag_id',
                'frontend_label' => $offerTagsHelper->__('Offer Tags'),
                'position' => -10,
                'frontend_input' => 'select',
                'is_visible_on_front' => 1,
                'store_label' => $offerTagsHelper->__('Offers'),
                'source' => $this->getSource(),
                'frontend_display_type' => Peppermint_Catalog_Helper_Filter::ATTRIBUTE_FRONTEND_DISPLAY_TYPE_SWITCHER,
            ];

        $this->setData($data);
    }

    /**
     * Fake frontend for the fake attribute
     *
     * @return false|Mage_Core_Model_Abstract
     */
    public function getFrontend()
    {
        $frontend = Mage::getModel('peppermint_offertags/offerTagAttribute_frontend');
        $frontend->setAttribute($this);

        return $frontend;
    }

    /**
     * Fake source for the fake attribute
     *
     * @return Varien_Object
     */
    public function getSource()
    {
        $offerTagsHelper = Mage::helper('peppermint_offertags');

        $offerTags = $offerTagsHelper->toOptionArray(
            $offerTagsHelper->getOfferTagsArray()
        );

        return new Varien_Object([
            'all_options' => $offerTags,
        ]);
    }
}

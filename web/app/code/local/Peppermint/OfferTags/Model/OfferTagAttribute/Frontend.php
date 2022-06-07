<?php
/**
 * @category  Peppermint
 * @package   Peppermint_OfferTags
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_OfferTags_Model_OfferTagAttribute_Frontend extends Varien_Object
{
    protected $_options;
    protected $_attribute;

    /**
     * Peppermint_OfferTags_Model_OfferTagAttribute_Frontend constructor.
     */
    public function __construct()
    {
        $offerTagsHelper = Mage::helper('peppermint_offertags');
        $this->_options = $offerTagsHelper->getOfferTagsArray(false, true);
        $offerTags = $offerTagsHelper->toOptionArray($this->_options);

        $this->setData([
            'select_options' => $offerTags,
        ]);
    }

    /**
     * get offertags options for layered navigation
     *
     * @param $optionId
     * @return false
     */
    public function getOption($optionId)
    {
        return array_key_exists($optionId, $this->_options ) ? $optionId : false;
    }

    /**
     * Set attribute
     *
     * @param $attribute
     */
    public function setAttribute($attribute)
    {
        $this->_attribute = $attribute;
    }

    /**
     * Get attribute
     *
     * @return mixed
     */
    public function getAttribute()
    {
        return $this->_attribute;
    }
}
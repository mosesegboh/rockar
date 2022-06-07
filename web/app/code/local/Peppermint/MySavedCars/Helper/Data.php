<?php

/**
 * @category  Peppermint
 * @package   Peppermint\MySavedCars
 * @author    Jiraphong Witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_MySavedCars_Helper_Data extends Rockar_MySavedCars_Helper_Data
{
    /**
     * Regular expression for validating saved car name | extend parent to add accentes characters
     */
    const SAVED_CAR_ALLOWED_NAME_REGEX = '/^[a-zA-ZÀ-ÿ0-9_+-.,!@#$%^&*();=:{}£\/|~`<>!?\'"\[\]§\s ]+$/u';

    /**
     * Rewrite of parent function to use const $this SAVED_CAR_ALLOWED_NAME_REGEX
     *
     * {@inheritDoc}
     */
    public function validateCarName(array $params = [])
    {
        $error = false;

        if (!isset($params['name'])) {
            $params['name'] = '';
        }

        $params['name'] = preg_replace('!\s+!', ' ', trim($params['name']));
        $wishListHelper = Mage::helper('wishlist');

        if (empty($params['name'])) {
            $error = $wishListHelper->__('Please specify car name before saving it.');
        } elseif (strlen($params['name']) >= 255) {
            $error = $wishListHelper->__('Name is too long, up to 255 characters are allowed.');
        } elseif (!preg_match(self::SAVED_CAR_ALLOWED_NAME_REGEX, $params['name'])) {
            $error = $wishListHelper->__('Please use only allowed characters for car name.');
        }

        return $error;
    }

    /**
     * Rewrite of parent function to add form key
     *
     * {@inheritDoc}
     */
    public function getRemoveUrl($item)
    {
        return $this->_getUrl('rockar_savedcars/ajax/remove', [
                'item' => $item->getWishlistItemId(),
                '_secure' => Mage::app()->getStore()->isCurrentlySecure(),
                'form_key' => Mage::getSingleton('core/session')->getFormKey()
        ]);
    }

    /**
     * Get url to remove wishlist item by product id
     *
     * {@inheritDoc}
     */
    public function getRemoveByProductIdUrl($productId)
    {
        return $this->_getUrl('rockar_savedcars/ajax/removeByProductId', [
            'product_id' => $productId,
            '_secure' => Mage::app()->getStore()->isCurrentlySecure(),
            'form_key' => Mage::getSingleton('core/session')->getFormKey()
        ]);
    }
}

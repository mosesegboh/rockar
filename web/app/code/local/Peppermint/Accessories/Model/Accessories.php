<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Accessories
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Accessories_Model_Accessories extends Rockar_Accessories_Model_Accessories
{
    /**
     * Rewrite parent function to get url from image field
     *
     * @return string
     */
    public function getImageFullUrl()
    {
        return $this->getData('image') ?: '';
    }

    /**
     * Rewrite parent function to not save images on instance
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        return Mage_Core_Model_Abstract::_beforeSave();
    }

    /**
     * Rewrite parent function to not delete images from instance
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterDelete()
    {
        return Mage_Core_Model_Abstract::_afterDelete();
    }
}

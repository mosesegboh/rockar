<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Experiences
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Experiences_Model_Coupon_Format
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $formatsList = Mage::helper('peppermint_experiences/coupon')->getFormatsList();
        $result = [];

        foreach ($formatsList as $formatId => $formatTitle) {
            $result[] = [
                'value' => $formatId,
                'label' => $formatTitle
            ];
        }

        return $result;
    }
}

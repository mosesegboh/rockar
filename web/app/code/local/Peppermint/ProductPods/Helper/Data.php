<?php
/**
 * @category Peppermint
 * @package Peppermint\ProductPods
 * @author Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_ProductPods_Helper_Data extends Rockar_ProductPods_Helper_Data
{
    /**
     * Prepare car attributes including icons
     *
     * @return array
     */
    public function prepareCarAttributes()
    {
        $result = [];
        $baseMediaUrl =  Mage::getBaseUrl('media');

        foreach ($this->getCarAttributes() as $attributeCode => $podItem) {
            foreach (['icon', 'hover_icon', 'big_icon'] as $icon) {
                $podItem[$icon] = $baseMediaUrl . $podItem[$icon];
            }

            $result[$attributeCode] = $podItem;
        }

        return $result;
    }
}

<?php
/**
 * @category  Peppermint
 * @package   Peppermint\OrderCap
 * @author    Lika Sikharulia <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_OrderCap_Block_Adminhtml_OrderCap_Renderer_Brand
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * {@inheritdoc}
     */
    public function render(Varien_Object $row)
    {
        $brands = explode(',', $row->getData('brand_group'));
        $values = Mage::helper('peppermint_ordercap')->getBrandValuesArray();
        $line = '';

        foreach ($values as $value) {
            if (in_array($value['value'], $brands)) {
                $line .= $value['label'] . '<br />';
            }
        }

        return $line;
    }
}
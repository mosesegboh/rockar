<?php
/**
 * @category  Peppermint
 * @package   Peppermint_PartExchange
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_PartExchange_Block_Adminhtml_Promotions_Rule_Renderer_DiscountAmount
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * {@inheritdoc}
     */
    public function render(Varien_Object $row)
    {
        $value = (float) parent::_getValue($row);

        switch ($row->getData('simple_action')) {
            case 'by_percent':
                $value .= '%';
                break;
            case 'by_fixed':
                $value = Mage::getModel('directory/currency')->load(Mage::app()->getStore()->getBaseCurrencyCode())
                    ->formatPrecision($value, 2, [], false);
                break;
        }

        return $value;
    }
}

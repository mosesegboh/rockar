<?php
/**
 * @category  Peppermint
 * @package   Peppermint_CatalogRule
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_CatalogRule_Block_Adminhtml_Report_ApplicableRules_Renderer_ActionAmount
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * {@inheritdoc}
     */
    public function render(Varien_Object $row)
    {
        $value = (float) parent::_getValue($row);

        switch ($row->getData('action_operator')) {
            case 'by_percent':
            case 'to_percent':
                $value .= '%';
                break;
            case 'by_fixed':
            case 'to_fixed':
                $value = Mage::getModel('directory/currency')->load(Mage::app()->getStore()->getBaseCurrencyCode())
                    ->formatPrecision($value, 2, [], false);
                break;
        }

        return $value;
    }
}

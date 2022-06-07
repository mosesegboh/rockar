<?php
/**
 * @category  Peppermint
 * @package   Peppermint_ExtendedProductGrid
 * @author    Krists Dadzitis <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_ExtendedProductGrid_Block_Adminhtml_Catalog_Product_Renderer_FinancingAmount
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Renders financing products amount
     *
     * @param Varien_Object $row
     *
     * @return string
     */
    public function render(Varien_Object $row)
    {
        return $row->getData('finance_products_amount') ?: '0';
    }
}


<?php
/**
 * @category     Peppermint
 * @package      Peppermint_ExtendedProductGrid
 * @author       Krists Dadzitis <techteam@rockar.com>
 * @copyright    Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_ExtendedProductGrid_Block_Adminhtml_Catalog_Product_Renderer_Category
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Render product category in grid
     *
     * @param Varien_Object $row
     * @return mixed
     */
    public function render(Varien_Object $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());

        return Mage::getModel('peppermint_extendedproductgrid/adminhtml_system_config_source_categories')
            ->getCategoryNameFromIds($value);
    }
}
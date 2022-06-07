<?php

/**
 * @category     Peppermint
 * @package      Peppermint_ExtendedProductGrid
 * @author       Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_ExtendedProductGrid_Block_Adminhtml_Catalog_Product_Renderer_Image
extends Rockar_ExtendedProductGrid_Block_Adminhtml_Catalog_Product_Renderer_Image
{
    /**
     * render
     *
     * @param Varien_Object $row
     * @return mixed
     */
    public function render(Varien_Object $row)
    {
        $val = $row->getData($this->getColumn()->getIndex());
        return '<img src="' . str_replace('no_selection', '', $val) . '" width="60px"/>';
    }
}

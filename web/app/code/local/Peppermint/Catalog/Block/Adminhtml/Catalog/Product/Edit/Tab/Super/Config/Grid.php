<?php
/**
* @category     Peppermint
* @package      Peppermint_Catalog
* @author       Andrian Kogoshvili <techteam@rockar.com>
* @copyright    Copyright (c) 2021 Rockar Ltd (http://rockar.com)
*/

class Peppermint_Catalog_Block_Adminhtml_Catalog_Product_Edit_Tab_Super_Config_Grid
    extends Rockar_Catalog_Block_Adminhtml_Catalog_Product_Edit_Tab_Super_Config_Grid
{
    /**
     * Get parameters for anchor
     *
     * @return array
     */
    public function getEditParamsForAssociated()
    {
        return [
            'base'      =>  '*/*/edit',
            'params'    =>  [
                'required' => $this->_getRequiredAttributesIds(),
                'popup'    => 0,
                'product'  => $this->_getProduct()->getId()
            ]
        ];
    }
}

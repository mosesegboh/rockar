<?php
/**
 * @category  Peppermint
 * @package   Peppermint\Catalog
 * @author    Andrian Kogoshvili <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Catalog_Block_Adminhtml_Catalog_Product_Edit_Tab_Options_Type_Select
    extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Options_Type_Select
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('catalog/product/edit/options/type/select.phtml');
        $this->setCanEditPrice(true);
        $this->setCanReadPrice(true);
    }
}

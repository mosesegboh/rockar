<?php
/**
 * @category  Peppermint
 * @package   Peppermint_ExtendedProductGrid
 * @author    Krists Dadzitis <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_ExtendedProductGrid_Block_Adminhtml_Catalog_Product_Grid
    extends Rockar_ExtendedProductGrid_Block_Adminhtml_Catalog_Product_Grid
{
    /**
     * Rewrite parent function to stop Rockar_ExtendedProductGrid from
     * modifying the collection
     *
     * {@inheritDoc}
     */
    protected function _afterLoadCollection()
    {
        Mage_Adminhtml_Block_Catalog_Product_Grid::_afterLoadCollection();

        return $this;
    }
}

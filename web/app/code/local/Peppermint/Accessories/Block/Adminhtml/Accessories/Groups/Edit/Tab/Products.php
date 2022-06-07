<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Accessories
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Accessories_Block_Adminhtml_Accessories_Groups_Edit_Tab_Products
    extends Rockar_Accessories_Block_Adminhtml_Accessories_Groups_Accessories_Edit_Tab_Products
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Returns data array with selected products info
     *
     * @return array
     */
    public function getSelectedProducts()
    {
        $products = [];
        $relationSkus = [];

        $selected = Mage::registry('accessory')->getSelectedProducts();

        if (!is_array($selected)) {
            return $products;
        }

        foreach ($selected as $relation) {
            $relationSkus[] = $relation->getProductSku();
        }

        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToFilter('sku', ['in' => $relationSkus]);

        foreach ($collection as $product) {
            $products[$product->getId()] = [];
        }

        return $products;
    }
}
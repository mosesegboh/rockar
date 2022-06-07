<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020  Rockar, Ltd (https://rockar.com)
 */

class Peppermint_Sales_Model_Order_Item extends Mage_Sales_Model_Order_Item
{
    /**
     * Retrieve product. Uses sku if the product is not found by id
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if (!$this->getData('product')) {
            $productModel = Mage::getModel('catalog/product');
            $product = $productModel->load($this->getProductId());

            if (!$product->getId()) {
                $sku = strtok($this->getData('sku'), '-');
                $productId = $productModel->getIdBySku($sku);

                if ($productId) {
                    $product = $productModel->load($productId);
                }
            }

            $this->setProduct($product);
        }

        return $this->getData('product');
    }
}

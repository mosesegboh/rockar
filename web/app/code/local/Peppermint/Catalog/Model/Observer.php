<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Catalog
 * @author    Jiraphong witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Catalog_Model_Observer extends Rockar2_Catalog_Model_Observer
{
    /**
     * @var string
     */
    protected $_modelMatrixTableAlias = 'model_matrix_carousel_idx';

    /**
     * Remove model matrix attribute value filter from the join clause to validate all
     * products with monthly price calc. This is to get the right product/filters count
     *
     * @param Varien_Event_Observer $observer
     * @return void
     *
     * @throws Zend_Db_Select_Exception
     */
    public function removeModelMatrixValueFromJoinClause(Varien_Event_Observer $observer)
    {
        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = $observer->getCollection();
        $fromClause = $collection->getSelect()->getPart(Varien_Db_Select::FROM);

        foreach ($fromClause as $name => $fromCondition) {
            if (strpos($name, $this->_modelMatrixTableAlias) !== false) {
                $isFound = true;
                $fromCondition['joinCondition'] = "{$name}.entity_id = e.entity_id";
                $fromClause[$name] = $fromCondition;
            }
        }

        if (isset($isFound)) {
           $collection->getSelect()->setPart(Varien_Db_Select::FROM, $fromClause);
        }
    }

    /**
     * {@inheritDoc}
     *
     * @todo Remove this rewrite when core version is update which include this ticket
     * `https://rockar.atlassian.net/browse/CORE-1075`
     */
    public function sortRegularCollection(Varien_Event_Observer $observer)
    {
        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = $observer->getCollection();
        $sortDirection = Mage::helper('rockar2_catalog/collection_sort')->getSortDirection();

        $sortFields = $collection->getSelect()->getPart(Zend_Db_Select::ORDER);
        // Rewrite here | check if $sortFields[0] is an array before accessing index [0]
        $sortExpression = is_array($sortFields[0] ?? false)
            ? "{$sortFields[0][0]} {$sortDirection}"
            : $sortFields[0] ?? false;

        if ($sortExpression) {
            // Remove any order by statements
            $collection->getSelect()->reset(Zend_Db_Select::ORDER);
            $collection->getSelect()->order(new Zend_Db_Expr($sortExpression));
        }
    }

    /**
     * Add customer email to the options data if logged in
     *
     * @param Varien_Event_Observer $observer
     */
    public function addOptionsData(Varien_Event_Observer $observer)
    {
        $options = $observer->getOptions();

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $options->setData('customerEmail', Mage::getSingleton('customer/session')->getCustomer()->getEmail());
        }
    }

    /**
     * Rewrite of parent function to add default exterior image for POD
     *
     * {@inheritDoc}
     */
    public function updateProductList(Varien_Event_Observer $observer)
    {
        $product = $observer->getData('product');
        $data = $observer->getData('data');

        $leadTimeHelper = Mage::helper('rockar_lead_time');
        /** @var Rockar_Catalog_Helper_Images $imagesHelper */
        $imagesHelper = Mage::helper('rockar_catalog/images');

        $product = $imagesHelper->loadProductMediaGallery($product);
        $images = $imagesHelper->getProductMedia($product);
        $images = $this->_prepareImages($images);

        $productRrp = (float)$product->getRrp();
        $productOptionsPrice = (float)$product->getOptionsPrice();
        $productFinalPrice = (float)$product->getFinalPrice();

        if ($productOptionsPrice !== 0 && $product->getTypeId() === Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)  {
            $saveOffRrp =  ($productRrp > 0) ? $productRrp - ($productOptionsPrice  + $productFinalPrice) : 0;
        } else {
            $saveOffRrp = ($productRrp > 0) ? $productRrp + $productOptionsPrice  - $productFinalPrice : 0;
        }

        $data->addData([
            'saveOffRrp' => $saveOffRrp,
            'images' => [
                [
                    'title' => 'Exterior',
                    'images' => $images[Rockar_Catalog_Helper_Images::IMAGE_TYPE_EXTERIOR] ?? []
                ],
                [
                    'title' => 'Interior',
                    'images' => $images[Rockar_Catalog_Helper_Images::IMAGE_TYPE_INTERIOR] ?? []
                ],
                [
                    'title' => 'Default Exterior',
                    'images' => $images[Peppermint_Catalog_Helper_Images::IMAGE_TYPE_DEFAULT_EXTERIOR_URL] ?? []
                ],
            ],
            'leadTime' => $product->getData('cheapest_product_lead_time')
                ? (int)$product->getData('cheapest_product_lead_time')
                : (int)$leadTimeHelper->getLeadTime($product, false),
            'optionalFinalPayment' => (float)$product->getData('data_optional_final_payment'),
            'productColorName' => $product->getColorName(),
            'apr' => (float)$product->getData('apr')
        ]);
    }
}

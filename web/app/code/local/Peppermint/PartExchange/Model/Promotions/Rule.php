<?php
/**
 * @category  Peppermint
 * @package   Peppermint_PartExchange
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_PartExchange_Model_Promotions_Rule extends Rockar_PartExchange_Model_Promotions_Rule
{
    /**
     * Store matched product Ids
     *
     * @var array
     */
    protected $_productIds;

    /**
     * Limitation for products collection
     *
     * @var int|array|null
     */
    protected $_productsFilter = null;

    /**
     * Filtering products that must be checked for matching with rule
     *
     * @param int|array $productIds
     */
    public function setProductsFilter($productIds)
    {
        $this->_productsFilter = $productIds;
    }

    /**
     * Returns products filter
     *
     * @return array|int|null
     */
    public function getProductsFilter()
    {
        return $this->_productsFilter;
    }

    /**
     * Prepare select for action (product) condition
     *
     * @param int $storeId
     * @return Varien_Db_Select
     */
    public function getProductFlatSelect($storeId)
    {
        /** @var $resource Mage_Rule_Model_Resource_Abstract */
        $resource = $this->getResource();

        return $resource->getProductFlatSelect($storeId, $this->getActions());
    }

    /**
     * Get array of product ids which are matched by rule by actions
     *
     * @return array
     */
    public function getMatchingProductIds()
    {
        if (is_null($this->_productIds)) {
            $this->_productIds = [];
            $this->setCollectedAttributes([]);

            if ($this->getWebsiteIds()) {
                /** @var $productCollection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection */
                $productCollection = Mage::getResourceModel('catalog/product_collection');
                $productCollection->addWebsiteFilter($this->getWebsiteIds());

                if ($this->_productsFilter) {
                    $productCollection->addIdFilter($this->_productsFilter);
                }

                $this->getActions()->collectValidatedAttributes($productCollection);

                Mage::getSingleton('core/resource_iterator')->walk(
                    $productCollection->getSelect(),
                    [[$this, 'callbackValidateProduct']],
                    [
                        'attributes' => $this->getCollectedAttributes(),
                        'product' => Mage::getModel('catalog/product')
                    ]
                );
            }
        }

        return $this->_productIds;
    }

    /**
     * Callback function for product matching
     *
     * @param $args
     * @return void
     */
    public function callbackValidateProduct($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);
        $results = [];

        foreach ($this->_getWebsitesMap() as $websiteId => $defaultStoreId) {
            $product->setStoreId($defaultStoreId);
            $results[$websiteId] = (int) $this->getActions()
                ->validate($product);
        }

        $this->_productIds[$product->getId()] = $results;
    }

    /**
     * Prepare website to default assigned store map
     *
     * @return array
     */
    protected function _getWebsitesMap()
    {
        $map = [];

        foreach ($this->_app->getWebsites(true) as $website) {
            if ($website->getDefaultStore()) {
                $map[$website->getId()] = $website->getDefaultStore()
                    ->getId();
            }
        }

        return $map;
    }
}

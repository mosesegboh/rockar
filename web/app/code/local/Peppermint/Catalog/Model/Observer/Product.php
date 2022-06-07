<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Catalog
 * @author    Osama Ahmed <osama.ahmed@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

class Peppermint_Catalog_Model_Observer_Product
{
    /**
     * Look for any mismatched entity ids and clear it the flat tables
     * To allow product flat reindex to work
     *
     * @param  Varien_Event_Observer $observer
     *
     * @return void
     */
    public function syncEntityAndFlat(Varien_Event_Observer $observer)
    {
        Mage::helper('peppermint_catalog/product_flat')->flatTablesCleanUp();
    }

    /**
     * Sanitize product data
     *
     * @param Varien_Event_Observer $observer
     */
    public function sanitizeProductData(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        Mage::helper('peppermint_all')->sanitizeData($product, [
            'standard_features',
            'technical_specification',
            'title',
            'subtitle',
            'short_title',
            'short_subtitle',
            'description',
            'short_description'
        ]);
    }
}

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_CatalogRule
 * @author    Dominic Sutton <dominic.sutton@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_CatalogRule_Model_Cron
{
    /**
     * Resave all incorrectly indexed attributed products, then reindex attributes
     *
     * @return void
     */
    public function run()
    {
        $model = Mage::getModel('catalog/product');

        $query = '
        select distinct p.entity_id
        from catalog_product_entity p
        left join catalog_product_entity_int i on p.entity_id = i.entity_id and i.attribute_id = 96
        where p.type_id = "configurable"
        and i.value = 1
        and p.entity_id not in (select distinct entity_id from catalog_product_index_eav where attribute_id = 282)
        union
        select distinct p.entity_id
        from catalog_product_entity p
        left join catalog_product_entity_int i on p.entity_id = i.entity_id and i.attribute_id = 96
        where p.type_id = "configurable"
        and i.value = 1
        and p.entity_id not in (select distinct entity_id from catalog_product_index_eav where attribute_id = 289)
        union
        select p.entity_id from catalog_product_entity p
        left join catalog_category_product_index i on i.product_id = p.entity_id
        left join catalog_product_entity_int iz on p.entity_id = iz.entity_id and iz.attribute_id = 96
        where p.type_id = "configurable" and iz.value = 1
        group by p.entity_id
        having (select count(*) from catalog_category_product_index where product_id = p.entity_id) <= 0;
        ';

        $products = Mage::getSingleton('core/resource')->getConnection('core_read')
            ->fetchAll($query);

        foreach ($products as $product) {
            $model->load($product['entity_id'])
                ->save();
        }

        Mage::getModel('index/process')->load(1)
            ->reindexAll();
        Mage::getModel('index/process')->load(6)
            ->reindexAll();
    }
}

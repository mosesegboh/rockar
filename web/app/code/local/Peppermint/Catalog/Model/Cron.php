<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Catalog
 * @author    Dominic Sutton <dominic.sutton@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Catalog_Model_Cron
{
    /**
     * Recompile configurable product group price and lead times
     *
     * @return void
     */
    public function run()
    {
        $query = '
        truncate catalog_product_entity_group_price;
        insert into catalog_product_entity_group_price (entity_id, all_groups, customer_group_id, value, website_id, is_percent)
            select
                p.entity_id as entity_id,
                0 as all_groups,
                z.customer_group_id as customer_group_id,
                min(z.rule_price) as value,
                0 as website_id,
                0 as is_percent
            from catalog_product_entity p
            inner join catalog_product_super_link l on p.entity_id = l.parent_id
            inner join catalogrule_product_price z on l.product_id = z.product_id
            inner join catalog_product_entity_int i on l.product_id = i.entity_id and i.attribute_id = 96 and i.value = 1
            where p.type_id = "configurable"
            group by p.entity_id, z.customer_group_id;

        truncate catalog_product_index_group_price;
        insert into catalog_product_index_group_price (entity_id, customer_group_id, website_id, price)
            select
                p.entity_id as entity_id,
                z.customer_group_id as customer_group_id,
                1 as website_id,
                min(z.rule_price) as price
                from catalog_product_entity p
                inner join catalog_product_super_link l on p.entity_id = l.parent_id
                inner join catalogrule_product_price z on l.product_id = z.product_id
                inner join catalog_product_entity_int i on l.product_id = i.entity_id and i.attribute_id = 96 and i.value = 1
                where p.type_id = "configurable"
                group by p.entity_id, z.customer_group_id
            union
            select
                p.entity_id as entity_id,
                z.customer_group_id as customer_group_id,
                2 as website_id,
                min(z.rule_price) as price
                from catalog_product_entity p
                inner join catalog_product_super_link l on p.entity_id = l.parent_id
                inner join catalogrule_product_price z on l.product_id = z.product_id
                inner join catalog_product_entity_int i on l.product_id = i.entity_id and i.attribute_id = 96 and i.value = 1
                where p.type_id = "configurable"
                group by p.entity_id, z.customer_group_id
            union
            select
                p.entity_id as entity_id,
                z.customer_group_id as customer_group_id,
                3 as website_id,
                min(z.rule_price) as price
                from catalog_product_entity p
                inner join catalog_product_super_link l on p.entity_id = l.parent_id
                inner join catalogrule_product_price z on l.product_id = z.product_id
                inner join catalog_product_entity_int i on l.product_id = i.entity_id and i.attribute_id = 96 and i.value = 1
                where p.type_id = "configurable"
                group by p.entity_id, z.customer_group_id
            union
            select
                p.entity_id as entity_id,
                z.customer_group_id as customer_group_id,
                4 as website_id,
                min(z.rule_price) as price
                from catalog_product_entity p
                inner join catalog_product_super_link l on p.entity_id = l.parent_id
                inner join catalogrule_product_price z on l.product_id = z.product_id
                inner join catalog_product_entity_int i on l.product_id = i.entity_id and i.attribute_id = 96 and i.value = 1
            where p.type_id = "configurable"
            group by p.entity_id, z.customer_group_id;

        drop table if exists peppermint_configurable_lead_time_tmp;
        create table peppermint_configurable_lead_time_tmp (
            parent_id int(6) unsigned,
            child_id int(6) unsigned,
            lead_time int(6) unsigned,
            price float(10,2) unsigned,
            vin_number varchar(255)
        );

        insert into peppermint_configurable_lead_time_tmp
            select * from (
                select
                    p.entity_id as parent_id,
                    sp.entity_id as child_id,
                    ifnull(l.available_in, greatest(datediff(l.available_on, curdate()), l.minimum_days)) as lead_time,
                    pp.rule_price as price,
                    l.identifier
                from catalog_product_entity p
                inner join catalog_product_super_link s on p.entity_id = s.parent_id
                inner join catalog_product_entity sp on s.product_id = sp.entity_id
                inner join catalogrule_product_price pp on sp.entity_id = pp.product_id
                inner join rockar_lead_time l on sp.sku = l.identifier
                where p.type_id = "configurable" and l.amount > 0
                group by p.entity_id, pp.rule_price, lead_time
            ) as tmp
            group by parent_id
            having min(tmp.price) and min(lead_time);

        insert into peppermint_configurable_lead_time_tmp
            select * from (
                select
                    p.entity_id as parent_id,
                    sp.entity_id as child_id,
                    ifnull(lt.available_in, greatest(datediff(lt.available_on, curdate()), lt.minimum_days)) as lead_time,
                    d.value as price,
                    lt.identifier
                from catalog_product_entity p
                left join catalog_product_super_link l on p.entity_id = l.parent_id
                left join catalog_product_entity sp on l.product_id = sp.entity_id
                left join catalog_product_entity_int i on sp.entity_id = i.entity_id and i.attribute_id = 96 and i.value != 2
                inner join catalog_product_entity_decimal d on l.product_id = d.entity_id and d.attribute_id = 75
                inner join rockar_lead_time lt on sp.sku = lt.identifier
                where p.type_id = "configurable" and lt.amount > 0 and parent_id not in (select parent_id from peppermint_configurable_lead_time_tmp)
                group by parent_id, d.value
            ) as tmp
            group by parent_id
            having min(tmp.price) and min(lead_time);

        delete from catalog_product_entity_varchar where attribute_id = 290 or attribute_id = 277;
        replace into catalog_product_entity_varchar (entity_type_id, attribute_id, store_id, entity_id, value)
            select
                4 as entity_type_id,
                290 as attribute_id,
                0 as store_id,
                parent_id as entity_id,
                ceiling(lead_time / 7) as value
                from peppermint_configurable_lead_time_tmp
                group by parent_id
                having min(price)
            union
            select
                4 as entity_type_id,
                277 as attribute_id,
                0 as store_id,
                parent_id as entity_id,
                ceiling(lead_time / 7) as value
                from peppermint_configurable_lead_time_tmp
                group by parent_id
                having min(price)
            union
            select
                4 as entity_type_id,
                403 as attribute_id,
                0 as store_id,
                parent_id as entity_id,
                vin_number as value
                from peppermint_configurable_lead_time_tmp
                group by parent_id
                having min(price)
            union
            select
                4 as entity_type_id,
                359 as attribute_id,
                0 as store_id,
                parent_id as entity_id,
                SUBSTR(vin_number, 10) as value
                from peppermint_configurable_lead_time_tmp
                group by parent_id
                having min(price);

        drop table if exists peppermint_configurable_lead_time_tmp;
        ';

        Mage::getSingleton('core/resource')->getConnection('core_write')->query($query);
    }

    /**
     * Delete empty configurable products
     *
     * @return void
     */
    public function deleteEmptyConfigurable()
    {
        try {
            $coreResource = Mage::getSingleton('core/resource');
            $writeConnection = $coreResource->getConnection('core_write');

            $select = $writeConnection->select()
                ->from(['e' => $coreResource->getTableName('catalog/product')], ['entity_id'])
                ->where($writeConnection->quoteInto(
                    'entity_id NOT IN (?)',
                    $writeConnection->select()
                        ->from(
                            Mage::getResourceSingleton('catalog/product_type_configurable')->getMainTable(),
                            ['parent_id as entity_id']
                        )
                ))->where('type_id =?', Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE);

            $writeConnection->query($writeConnection->deleteFromSelect($select, 'e'));

            Mage::helper('peppermint_catalog/product_flat')->flatTablesCleanUp();
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Look for any mismatched entity ids and clear it from the flat tables
     * To allow product flat reindex to work
     *
     * @return void
     */
    public function flatTablesSync()
    {
        Mage::helper('peppermint_catalog/product_flat')->flatTablesCleanUp();
    }
}

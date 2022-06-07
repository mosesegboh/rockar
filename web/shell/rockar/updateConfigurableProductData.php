<?php
/**
 * Script for updating configurable products with random simple data
 *
 * @category  Peppermint
 * @package   Peppermint_Shell
 * @author    Dominic Sutton <dominic.sutton@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

require_once dirname(__FILE__) . '/../../app/Mage.php';
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

/**
 * Remove Attributes
 * @param  Array &$array
 * @return void
 */
function removeAttributes(&$array)
{
    $attributesToClear = [
        'entity_id',
        'created_at',
        'updated_at',
        'vin_number',
        'stock_item',
        'short_vin_number',
        'sku',
        'url_key',
        'entity_type_id',
        'attribute_set_id',
        'type_id',
        'url_path'
    ];

    foreach ($attributesToClear as $attr) {
        unset($array[$attr]);
    }

    unset($attributesToClear);
}

/**
 * Update Configurable
 * @param  integer  $configurableId
 * @param  integer  $simpleId
 * @param  integer $current
 * @param  integer $max
 * @return void
 */
function updateConfigurable($configurableId, $simpleId, $current = 0, $max = 0)
{
    try {
        $simpleData = Mage::getModel('catalog/product')->load($simpleId)
            ->getData();
        removeAttributes($simpleData);
        $simpleData['entity_id'] = $configurableId;
        $configurableProduct = Mage::getModel('catalog/product')->load($configurableId)
            ->setData($simpleData);
        $configurableProduct->getResource()
            ->save($configurableProduct);
        unset($simpleData);
        unset($configurableProduct);
        echo '\033[1;37m' . date('H:i:s') . ' [' . $current . '/' . $max . '] Updated ' . $configurableId . ' with data from ' . $simpleId . '\033[0m' . PHP_EOL;
    } catch (Exception $e) {
        echo '\033[1;31m' . date('H:i:s') . ' [' . $current . '/' . $max . '] Failed to update ' . $configurableId . ' with data from ' . $simpleId . '. Error: ' . $e->getMessage() . '\033[0m' . PHP_EOL;
    }
}


$resource = Mage::getSingleton('core/resource')->getConnection('core_read');
$query = '
select p.entity_id, c.product_id
from catalog_product_entity p
inner join catalog_product_super_link c on p.entity_id = c.parent_id
group by p.entity_id
';

$results = $resource->fetchAll($query);
unset($query);
unset($resource);
gc_collect_cycles();

$length = count($results);
for ($i = 0; $i < $length - 1; $i++) {
    updateConfigurable($results[$i]['entity_id'], $results[$i]['product_id'], $i + 1, $length);
    gc_collect_cycles();
}

unset($results);
unset($length);
gc_collect_cycles();

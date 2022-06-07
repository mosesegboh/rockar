<?php

/**
 * @category     Peppermint
 * @package      Peppermint_Setup
 * @author       Adrian Grigorita <adrian.grigorita@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();

/**
 * Change catalog_product_entity_media_gallery value column from VARCHAR to TEXT, because cosyurl is longer then 255
 */
$connection->modifyColumn($this->getTable('catalog_product_entity_media_gallery'), 'value', 'TEXT NULL DEFAULT NULL COMMENT "Value" AFTER `entity_id`');

/**
 * Change image ,default_exterior ,default_interior ,small_image ,thumbnail attributes from VARCHAR to TEXT, because cosyurl is longer then 255
 */
$connection->update(
    $this->getTable('eav_attribute'),
    ['backend_type' => 'text'],
    'attribute_code="image" or attribute_code="default_exterior" or attribute_code="default_interior" or attribute_code="small_image" or attribute_code="thumbnail"'
);

/**
 * Change thumbnail ,small_image ,default_exterior attributes from VARCHAR to TEXT on all flat tbles, because cosyurl is longer then 255
 */
$columns = [
    'thumbnail' => 'Thumbnail',
    'small_image' => 'Small Image',
    'default_exterior' => 'Default exterior'
];
$tables = [
    'catalog_product_flat_1',
    'catalog_product_flat_2',
    'catalog_product_flat_3',
    'catalog_product_flat_4'
];
foreach ($columns as $column => $comment) {
    foreach ($tables as $table) {
        if ($installer->tableExists($table)) {
            $connection->modifyColumn($this->getTable($table), $column, 'TEXT NULL DEFAULT NULL COMMENT "' . $comment . '"');
        }
    }
}
$installer->endSetup();

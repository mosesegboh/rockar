<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Accessories
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

/** @var $this Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();
$accessoriesRelationsTableName = $installer->getTable('rockar_accessories/accessories_relations');
$fkName = $installer->getFkName(
    $accessoriesRelationsTableName,
    'product_sku',
    $installer->getTable('catalog/product'),
    'sku'
);

$connection->dropForeignKey(
    $accessoriesRelationsTableName,
    $fkName
);

$installer->endSetup();

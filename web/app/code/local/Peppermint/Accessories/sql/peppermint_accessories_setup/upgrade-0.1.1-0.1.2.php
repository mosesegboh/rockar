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
$uniqueColumnNames = ['product_sku', 'accessory_id'];

$connection->addIndex(
    $accessoriesRelationsTableName,
    $installer->getIdxName($accessoriesRelationsTableName,
        $uniqueColumnNames,
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    $uniqueColumnNames,
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$installer->endSetup();

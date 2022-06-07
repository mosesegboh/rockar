<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Reports
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$pricingSnapshotTable = $installer->getTable('peppermint_reports/vin_product_pricing_snapshot');

$connection = $installer->getConnection();
$installer->startSetup();

$connection
    ->addForeignKey(
        $installer->getFkName(
            $pricingSnapshotTable,
            'product_id',
            'catalog/product',
            'entity_id'
        ),
        $pricingSnapshotTable,
        'product_id',
        $installer->getTable('catalog/product'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    );

$installer->endSetup();

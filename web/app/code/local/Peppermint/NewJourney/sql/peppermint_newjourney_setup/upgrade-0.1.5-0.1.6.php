<?php
/**
 * @category  Peppermint
 * @package   Peppermint_ProductPods
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();
$tableName = $installer->getTable('rockar_product_pods/item');

if (!$connection->tableColumnExists($tableName, 'label')) {
    $connection->addColumn(
        $tableName,
        'label',
        [
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => 255,
            'nullable' => true,
            'default' => null,
            'comment' => 'Product Pod Item Label'
        ]
    );
}

$installer->endSetup();

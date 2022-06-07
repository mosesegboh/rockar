<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Localstores
 * @author    Lika Sikharulia <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();

$table = $installer->getTable('rockar_localstores/stores');
$connection->addColumn(
    $table,
    'order_cap',
    [
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned'  => true,
        'nullable'  => true,
        'default'   => '0',
        'comment'   => 'Retailers active order cap'
    ]
);

$installer->endSetup();

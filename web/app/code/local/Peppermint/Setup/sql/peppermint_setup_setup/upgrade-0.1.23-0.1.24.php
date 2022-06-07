<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Setup
 * @author    Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();

$tableName = $installer->getTable('rockar_financingoptions/group');

$columns = [
    'website',
    'method_type'
];

foreach ($columns as $key => $columnName) {
    if (!$connection->tableColumnExists($tableName, $columnName)) {
        $connection->addColumn(
            $tableName,
            $columnName,
            'SMALLINT(6) DEFAULT 0'
        );
    }
}

$installer->endSetup();

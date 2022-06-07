<?php
/**
 * @category  Peppermint
 * @package   Peppermint_ShortfallAllowance
 * @author    Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();
$tableName = $installer->getTable('peppermint_shortfallallowance/shortfall_allowance');

$table = $connection
    ->newTable($tableName)->addColumn(
        'id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        [
            'identity' => true,
            'nullable' => false,
            'unsigned' => true,
            'primary' => true
        ],
        'Primary Key Id'
    )
    ->addColumn(
        'models',
        Varien_Db_Ddl_Table::TYPE_TEXT,
        256,
        ['nullable' => false],
        'Models from model_code attribute'
    )
    ->addColumn(
        'shortfall_limit',
        Varien_Db_Ddl_Table::TYPE_DECIMAL,
        '10,2',
        ['nullable' => false],
        'Shortfall Limit'
    );
$connection->createTable($table);
$installer->endSetup();

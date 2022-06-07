<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();

$tableName = $installer->getTable('rockar_financingoptions/group');
if (!$connection->tableColumnExists($tableName, 'balloon_slider_steps')) {
    $connection->addColumn(
        $tableName,
        'balloon_slider_steps',
        'TEXT DEFAULT NULL'
    );
}
if (!$connection->tableColumnExists($tableName, 'balloon_default_step')) {
    $connection->addColumn(
        $tableName,
        'balloon_default_step',
        'TEXT DEFAULT NULL'
    );
}
$installer->endSetup();

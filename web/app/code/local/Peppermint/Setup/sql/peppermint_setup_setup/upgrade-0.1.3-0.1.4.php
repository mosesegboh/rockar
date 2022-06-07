<?php

/**
 * @category     Peppermint
 * @package      Peppermint_Setup
 * @author       Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();
$tableName = $installer->getTable('rockar_extendedrules/colour');
/* Empty data for deprecated table rockar_extendedrules_colour */
if ($connection->isTableExists($tableName)) {
    $connection->truncateTable($tableName);
}

$installer->endSetup();

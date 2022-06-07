<?php
/**
 * @category     Peppermint
 * @package      Peppermint_YouDrive
 * @author       Taras Kapushchak <techteam@rockar.com>
 * @copyright    Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();

$installer->getConnection()->changeColumn(
    $installer->getTable('rockar_youdrive/vehicle'),
    'assigned_to',
    'assigned_to',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 255
    )
);

$installer->endSetup();

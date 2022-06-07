<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Accessories
 * @author    Krists Dadzitis <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

$this->startSetup();

$table = $this->getTable('rockar_accessories/accessories');
$connection = $this->getConnection();

// Adding option_code column to rockar_accessories table
if (!$connection->tableColumnExists($table, 'option_code')) {
    $connection->addColumn($table, 'option_code', [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 255,
        'nullable' => true,
        'comment' => 'Option code',
        'after' => 'identifier'
    ]);
}

$this->endSetup();
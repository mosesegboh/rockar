<?php

/**
 * @category     Peppermint
 * @package      Peppermint\AschroderEmail
 * @author       Catalin Lungu <catalin.lungu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;

/** @var String $table */
$table = $installer->getTable('aschroder_email/email_log');

/** @var Varien_Db_Adapter_Interface $connection */
$connection = $installer->getConnection();

$installer->startSetup();
$connection->addColumn($table, 'attachment_path', [
    'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
    'nullable'  => true,
    'comment'   => 'Attachment',
    'default'   => ''
]);
$installer->endSetup();

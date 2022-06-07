<?php
/**
 * @category  Peppermint
 * @package   Peppermint_LeadTime
 * @author    Taras Kapushchak <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$connection = $installer->getConnection();

$reservationTable = $installer->getTable('peppermint_leadtime/reservation');
$field = 'placing_order';

if (!$connection->tableColumnExists($reservationTable, $field)) {
    $connection->addColumn(
        $reservationTable,
        $field,
        [
            'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            'unsigned' => true,
            'nullable' => false,
            'default' => '0',
            'comment' => 'Field to detect place order action',
        ]
    );
}

$installer->endSetup();

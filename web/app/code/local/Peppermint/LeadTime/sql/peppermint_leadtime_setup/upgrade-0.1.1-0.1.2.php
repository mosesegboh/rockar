<?php
/**
 * @category  Peppermint
 * @package   Peppermint_LeadTime
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;

$connection = $installer->getConnection();
$leadTimesTable = $installer->getTable('rockar_lead_time/lead_time');
$hashField = 'hash';

if (!$connection->tableColumnExists($leadTimesTable, $hashField)) {
    $connection->addColumn($leadTimesTable, $hashField, 'VARCHAR(64) DEFAULT NULL COMMENT "Hash"');
}

// Lead Times view
$query = "
    DROP
      VIEW IF EXISTS `Lead Times`;
    CREATE VIEW `Lead Times` AS
    SELECT
        `lead_times`.`identifier` AS `vin`,
        `lead_times`.`hash` AS `hash`
    FROM {$leadTimesTable} AS `lead_times`
";

$connection->query($query);

$installer->endSetup();

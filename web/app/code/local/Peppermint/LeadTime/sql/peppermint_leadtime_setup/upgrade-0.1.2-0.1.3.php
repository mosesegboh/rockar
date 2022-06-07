<?php
/**
 * @category  Peppermint
 * @package   Peppermint_LeadTime
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;

$leadTimesTable = $installer->getTable('rockar_lead_time/lead_time');
$field = 'identifier';

$installer->getConnection()
    ->addIndex(
        $leadTimesTable,
        $installer->getIdxName($leadTimesTable, $field),
        $field,
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    );

$installer->endSetup();

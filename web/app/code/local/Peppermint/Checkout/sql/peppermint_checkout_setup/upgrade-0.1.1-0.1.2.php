<?php

/**
* @category  Peppermint
* @package   Peppermint\Checkout
* @author    Adrian Grigorita <adrian.grigorita@rockar.com>
* @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
*/

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();
$columnsToAdd = [
    'home_tel' => 'VARCHAR(15) DEFAULT NULL COMMENT "Home phone number"',
    'gender' => 'VARCHAR(5) DEFAULT NULL COMMENT "Gender"',
    'race' => 'VARCHAR(5) DEFAULT NULL COMMENT "Race"',
    'preferred_language' => 'VARCHAR(5) DEFAULT NULL COMMENT "Preferred Language"',
    'marriage_type' => 'VARCHAR(5) DEFAULT NULL COMMENT "Marriage Type"',
    'spouse_first_name' => 'VARCHAR(5) DEFAULT NULL COMMENT "Spouse First Name"',
    'spouse_last_name' => 'VARCHAR(100) DEFAULT NULL COMMENT "Spouse Last Name"',
    'spouse_id_type' => 'VARCHAR(5) DEFAULT NULL COMMENT "Spouse ID Type"',
    'spouse_id_no' => 'VARCHAR(20) DEFAULT NULL COMMENT "Spouse ID Type"',
    'spouse_cell_number' => 'VARCHAR(10) DEFAULT NULL COMMENT "Spouse Cell Number"',
    'spouse_email' => 'VARCHAR(100) DEFAULT NULL COMMENT "Spouse Email"',
    'kin_name' => 'VARCHAR(200) DEFAULT NULL COMMENT "Kin Name"',
    'kin_tel' => 'VARCHAR(10) DEFAULT NULL COMMENT "Kin Tel"',
    'spouse_consent' => 'INT(1) DEFAULT NULL COMMENT "Spouse consent"'
];
$tableName = $installer->getTable('rockar_checkout/quote_additional');
foreach ($columnsToAdd as $column => $dataType) {
    if (!$connection->tableColumnExists($tableName, $column)) {
        $connection->addColumn($tableName, $column, $dataType);
    }
}
$installer->endSetup();

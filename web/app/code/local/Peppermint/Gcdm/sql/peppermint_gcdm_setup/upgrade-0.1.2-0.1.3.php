<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Gcdm
 * @author    Adrian Pescar <adrian.pescar@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();

$customerProfileData = [
    'business_address_guid' => 'VARCHAR(36) NULL',
    'business_phone_guid' => 'VARCHAR(36) NULL',
    'vat_number_guid' => 'VARCHAR(36) NULL'
];
$customerProfileTableName = $installer->getTable('peppermint_gcdm/customer_profile');

foreach ($customerProfileData as $column => $dataType) {
    $connection->addColumn($customerProfileTableName, $column, $dataType);
}

$installer->endSetup();

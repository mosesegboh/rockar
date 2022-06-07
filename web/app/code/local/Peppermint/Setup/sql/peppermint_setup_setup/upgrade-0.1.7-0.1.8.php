<?php

/**
 * @category     Setup
 * @package      Peppermint_Setup
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$connection = $installer->getConnection();

$installer->startSetup();

$extendedRulesTables = [
    'rockar_extendedrules/brands',
    'rockar_extendedrules/priceRanges',
    'rockar_extendedrules/dayInMonth',
    'rockar_extendedrules/age',
    'rockar_extendedrules/colour',
    'rockar_extendedrules/exceptionCap',
    'rockar_extendedrules/exceptionBrand'
];

foreach ($extendedRulesTables as $table) {
    $tableName = $installer->getTable($table);

    if ($connection->isTableExists($tableName)) {
        $connection->truncateTable($tableName);
    }
}

$installer->endSetup();

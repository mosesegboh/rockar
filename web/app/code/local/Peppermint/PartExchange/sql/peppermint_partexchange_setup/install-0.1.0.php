<?php

/**
 * @category     Peppermint
 * @package      Peppermint_PartExchange
 * @author       Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();
$valuesToUpdate = [
    '1' => [
        'title' => 'Poor',
        'mapping' => 'clean'
    ],
    '2' => [
        'title' => 'Fair',
        'mapping' => 'clean'
    ],
    '3' => [
        'title' => 'Average',
        'mapping' => 'clean'
    ],
    '4' => [
        'is_default' => 0,
        'is_active' => 0,
        'mapping' => 'clean'
    ],
    '5' => [
        'title' => 'Good',
        'is_default' => 1,
        'mapping' => 'clean'
    ],
    '6' => [
        'title' => 'Excellent',
        'mapping' => 'clean'
    ]
];
$tableName = $installer->getTable('rockar_partexchange/conditions_slider');
$connection = $installer->getConnection();

foreach (Mage::getModel('rockar_partexchange/conditions_slider')->getCollection() as $key => $values) {
    if (isset($valuesToUpdate[$key])) {
        $connection->update($tableName, $valuesToUpdate[$key], '`entity_id`=' . $key);
    }
}

$installer->endSetup();

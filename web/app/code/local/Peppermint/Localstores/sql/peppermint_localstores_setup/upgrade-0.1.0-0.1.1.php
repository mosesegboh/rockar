<?php

/**
 * @category  Setup
 * @package   Peppermint\Localstores
 * @author    Ana-Maria Buliga <anamara.buliga@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();

/* Add new columns to rockar_localstores_stores table */
$table   = $installer->getTable('rockar_localstores/stores');
$columns = [
    'dealer_code' => [
        'comment'   => 'Dealer code',
        'after'     => 'entity_id'
    ],
    'associated_brand' => [
        'comment'   => 'Associated brand',
        'after'     => 'name'
    ],
    'associated_vehicle_types' => [
        'comment'   => 'Associated vehicle types',
        'after'     => 'associated_brand'
    ],
    'vehicle_types' => [
        'comment'   => 'Vehicle types',
        'after'     => 'associated_vehicle_types'
    ],
    'brand_code' => [
        'comment'   => 'Brand code',
        'after'     => 'vehicle_types'
    ],
    'financial_services_provider_number' => [
        'comment'   => 'Financial services provider number',
        'after'     => 'brand_code'
    ],
    'registered_company_name' => [
        'comment'   => 'Registered company name',
        'after'     => 'financial_services_provider_number'
    ],
    'branch' => [
        'comment'   => 'Branch',
        'after'     => 'registered_company_name'
    ],
    'branch_name' => [
        'comment'   => 'Branch name',
        'after'     => 'branch'
    ],
    'branch_type' => [
        'comment'   => 'Branch type',
        'after'     => 'branch_name'
    ],
    'brand' => [
        'comment'   => 'Brand',
        'after'     => 'branch_type'
    ]
];

foreach ($columns as $columnName => $columnData) {
    $connection->addColumn(
        $table,
        $columnName,
        [
            'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
            'nullable'  => true,
            'length'    => 255,
            'comment'   => $columnData['comment'],
            'after'     => $columnData['after']
        ]
    );
}

/* Add new columns to rockar_localstores_addresses table */
$table   = $installer->getTable('rockar_localstores/address');
$columns = [
    'postal_address_line_1' => [
        'comment'   => 'Postal address line 1',
        'after'     => 'latitude'
    ],
    'postal_address_line_2' => [
        'comment'   => 'Postal address line 2',
        'after'     => 'postal_address_line_1'
    ],
    'postal_address_line_3' => [
        'comment'   => 'Postal address line 3',
        'after'     => 'postal_address_line_2'
    ],
    'postal_address_city' => [
        'comment'   => 'Postal address city',
        'after'     => 'postal_address_line_3'
    ],
    'postal_address_postal_code' => [
        'comment'   => 'Postal address postal code',
        'after'     => 'postal_address_city'
    ],
    'postal_address_province' => [
        'comment'   => 'Postal address province',
        'after'     => 'postal_address_postal_code'
    ],
    'province_code' => [
        'comment'   => 'Province code',
        'after'     => 'email_address'
    ],
    'province_name' => [
        'comment'   => 'Province name',
        'after'     => 'province_code'
    ]
];

foreach ($columns as $columnName => $columnData) {
    $connection->addColumn(
        $table,
        $columnName,
        [
            'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
            'nullable'  => true,
            'length'    => 255,
            'comment'   => $columnData['comment'],
            'after'     => $columnData['after']
        ]
    );
}

$installer->endSetup();

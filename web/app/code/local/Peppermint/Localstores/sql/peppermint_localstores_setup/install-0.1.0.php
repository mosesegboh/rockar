<?php

/**
 * @category     Setup
 * @package      Peppermint_Setup
 * @author       Catalin Lungu <catalin.lungu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

/** @var String $table */
$table = $this->getTable('rockar_localstores/address');

$this->startSetup();
$columnsToAdd = [
    'legal_entity' => [
        'length' => 255,
        'comment' => 'Legal entity'
    ],
    'registration_number' => [
        'length' => 50,
        'comment' => 'Registration Number'
    ],
    'vat_number' => [
        'length' => 50,
        'comment' => 'VAT Number'
    ],
    'email_address' => [
        'length' => 255,
        'comment' => 'Dealer Email Address'
    ]
];
foreach ($columnsToAdd as $columnName => $options) {
    $this->getConnection()->addColumn(
        $table,
        $columnName,
        array_merge(
            [
                'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
                'nullable' => true
            ],
            $options
        )
    );
}
$this->endSetup();

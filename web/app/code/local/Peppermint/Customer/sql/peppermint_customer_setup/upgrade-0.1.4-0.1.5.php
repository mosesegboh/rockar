<?php
/*
* @category  Peppermint
* @package   Peppermint_Customer
* @author    Craig Goodspeed <techteam@rockar.com>
* @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
* @description The definition and schema below will allow documents to be contained to a customer
*              but in the same breath be associated with multiple orders. Allowing for maximum
*              flexibility with reduced complexity.
*               see design here https://dbdiagram.io/d/5f89b70e3a78976d7b77f1c3
*/

$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();
$documentTable =  $installer->getTable('rockar_customer/documents');
$documentLookupTable = $installer->getTable('peppermint_customer/document_type');
$documentTypeTable = $installer->getTable('peppermint_customer/document_type');
$documentFinancingType = $installer->getTable('peppermint_customer/document_finance_type');
$documentFinancingGroup = $installer->getTable('peppermint_customer/document_finance_group');
$documentOrder = $installer->getTable('peppermint_customer/document_order');
$documentOrderUploadStatus = $installer->getTable('peppermint_customer/document_upload_status');
$documentFinanceCustomerGroup = $installer->getTable('peppermint_customer/document_customer_group');

//metadata setup
$tables = [
    [
        'table' => $documentTypeTable,
        'columns' => [
            [
                'name' => 'id',
                'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'length' => 10,
                'attributes' => [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                ],
                'description' => 'The document type required for finance application.'
            ],
            [
                'name' => 'name',
                'type' => Varien_Db_Ddl_Table::TYPE_VARCHAR,
                'length' => 100,
                'attributes' => [
                    'identity' => false,
                    'nullable' => false,
                    'unique' => true
                ],
                'description' => 'Name for the document as to be displayed to the client.'
            ],
            [
                'name' => 'description',
                'type' => Varien_Db_Ddl_Table::TYPE_VARCHAR,
                'length' => 500,
                'attributes' => [
                    'identity' => false,
                    'nullable' => false
                ],
                'description' => 'Description for the document, additional data like why this is here.'
            ],
            [
                'name' => 'allow_multiple_uploads',
                'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'length' => 1,
                'attributes' => [
                    'identity' => false,
                    'nullable' => false
                ],
                'description' => 'Allow multiples of this document type to be uploaded'
            ],
            [
                'name' => 'finance_document_type_id',
                'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'length' => 1,
                'attributes' => [
                    'identity' => false,
                    'nullable' => false
                ],
                'description' => 'document type identity as defined by external systems'
            ]
        ]
    ],
    [
        'table' => $documentFinancingType,
        'columns' => [
            [
                'name' => 'id',
                'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'length' => 10,
                'attributes' => [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                ],
                'description' => 'The identity for document financing table'
            ],
            [
                'name' => 'finance_group_id',
                'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'length' => 10,
                'attributes' => [
                    'unsigned' => true,
                    'nullable' => false
                ],
            ],
            [
                'name' => 'document_type_id',
                'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'length' => 10,
                'attributes' => [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'description' => 'Associates a financing type with a document type'
            ],
            [
                'name' => 'applicable_to_customer_group_id',
                'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'length' => 10,
                'attributes' => [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'description' => 'Associates a financing type with a finance group, at the time of writing this it will mainly be credit or cash.'
            ]
        ]
    ],
    [
        'table' => $documentFinancingGroup,
        'columns' => [
            [
                'name' => 'id',
                'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'length' => 10,
                'attributes' => [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                ],
                'description' => 'The document finance group identifier, while writing this credit and cash BUT'
                                .' including this forces data integrity, allows for extension eg. we want to add'
                                .' bitcoin as a payment type'
            ],
            [
                'name' => 'name',
                'type' => Varien_Db_Ddl_Table::TYPE_VARCHAR,
                'length' => 100,
                'attributes' => [
                    'identity' => false,
                    'nullable' => false,
                    'unique' => true
                ],
                'description' => 'Name for the document finance group as to be displayed to the client.'
            ],
            [
                'name' => 'description',
                'type' => Varien_Db_Ddl_Table::TYPE_VARCHAR,
                'length' => 500,
                'attributes' => [
                    'identity' => false,
                    'nullable' => false
                ],
                'description' => 'Description for the document, additional data like why this is here.'
            ],
            [
                'name' => 'is_pay_in_full',
                'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'attributes' => [
                    'identity' => false,
                    'nullable' => false
                ],
                'description' => 'does this finance type pay for the vehicle in full?.'
            ]
        ]
    ],
    [
        'table' => $documentOrder,
        'columns' => [
            [
                'name' => 'id',
                'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'length' => 10,
                'attributes' => [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                ],
                'description' => 'identifier for the document_order table'
            ],
            [
                'name' => 'document_id',
                'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'length' => 10,
                'attributes' => [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'description' => 'document to be associated with this order.'
            ],
            [
                'name' => 'order_id',
                'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'length' => 10,
                'attributes' => [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'description' => 'order identity that this document is associated too.'
            ],
            [
                'name' => 'upload_status_id',
                'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'length' => 10,
                'attributes' => [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'description' => 'Has this been sent? Do we need to resend to imaging.'
            ],
            [
                'name' => 'date_created',
                'type' => Varien_Db_Ddl_Table::TYPE_DATETIME,
                'attributes' => [
                    'identity' => false,
                    'nullable' => false
                ]
            ],
            [
                'name' => 'date_updated',
                'type' => Varien_Db_Ddl_Table::TYPE_DATETIME,
                'attributes' => [
                    'identity' => false,
                    'nullable' => true
                ]
            ],
            [
                'name' => 'date_uploaded',
                'type' => Varien_Db_Ddl_Table::TYPE_DATETIME,
                'attributes' => [
                    'identity' => false,
                    'nullable' => true
                ]
            ]
        ]
    ],
    [
        'table' => $documentOrderUploadStatus,
        'columns' => [
            [
                'name' => 'id',
                'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'length' => 10,
                'attributes' => [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                ],
                'description' =>    'identifier for the document_order_upload_status table, '
                                    .'what errors/success messages have been returned'
            ],
            [
                'name' => 'name',
                'type' => Varien_Db_Ddl_Table::TYPE_VARCHAR,
                'length' => 100,
                'attributes' => [
                    'identity' => false,
                    'nullable' => false,
                    'unique' => true
                ],
                'description' => 'Name for the upload status to imaging.'
            ],
            [
                'name' => 'description',
                'type' => Varien_Db_Ddl_Table::TYPE_VARCHAR,
                'length' => 500,
                'attributes' => [
                    'identity' => false,
                    'nullable' => false
                ],
                'description' => 'Description for the upload status.'
            ],
            [
                'name' => 'uploaded',
                'type' => Varien_Db_Ddl_Table::TYPE_TINYINT,
                'attributes' => [
                    'identity' => false,
                    'nullable' => false,
                    'default' => 0
                ],
                'description' => 'Description for the upload status.'
            ]
        ]
    ],
    [
        'table' => $documentFinanceCustomerGroup,
        'columns' => [
            [
                'name' => 'id',
                'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'length' => 10,
                'attributes' => [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                ],
                'description' =>    'identifier for the document_customer_group table'
            ],
            [
                'name' => 'name',
                'type' => Varien_Db_Ddl_Table::TYPE_VARCHAR,
                'length' => 100,
                'attributes' => [
                    'identity' => false,
                    'nullable' => false,
                    'unique' => true
                ],
                'description' => 'Name for the document customer group.'
            ],
            [
                'name' => 'description',
                'type' => Varien_Db_Ddl_Table::TYPE_VARCHAR,
                'length' => 500,
                'attributes' => [
                    'identity' => false,
                    'nullable' => false
                ],
                'description' => 'Description for the customer group.'
            ],
            [
                'name' => 'is_company',
                'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'attributes' => [
                    'identity' => false,
                    'nullable' => false
                ],
                'description' => 'Indicator, is this customer a company or not?'
            ]
        ]
    ]
];

if ($connection->tableColumnExists($documentTable,'document_type_id')) {
    $connection
        ->dropColumn(
            $documentTable,
            'document_type_id'
        );
}

if ($connection->tableColumnExists($documentTable,'document_finance_type_id')) {
    $connection
        ->dropColumn(
            $documentTable,
            'document_finance_type_id'
        );
}

foreach ($tables as $table) {
    if ($connection->isTableExists($table['table'])) {
        $connection->dropTable($table['table']);
    }
}

foreach ($tables as $table) {
    $toCreate = $connection->newTable($table['table']);

    foreach ($table['columns'] as $col) {
        $toCreate->addColumn(
            $col['name'],
            $col['type'],
            $col['length'],
            $col['attributes'],
            $col['description']
        );
    }

    $connection->createTable($toCreate);
}

$connection
    ->addColumn(
        $documentTable,
        'document_type_id',
        'INTEGER(10) NULL'
    );

$connection
    ->addColumn(
        $documentTable,
        'document_finance_type_id',
        'INTEGER(10) NULL'
    );

$connection->addForeignKey(
    $installer->getFkName(
        $documentTable,
        'document_type_id',
        $documentTypeTable,
        'id'
    ),
    $documentTable,
    'document_type_id',
    $documentTypeTable,
    'id'
);

$connection->addForeignKey(
    $installer->getFkName(
        $documentOrder,
        'document_id',
        $documentTable,
        'entity_id'
    ),
    $documentOrder,
    'document_id',
    $documentTable,
    'entity_id'
);

$connection->addForeignKey(
    $installer->getFkName(
        $documentOrder,
        'upload_status_id',
        $documentOrderUploadStatus,
        'id'
    ),
    $documentOrder,
    'upload_status_id',
    $documentOrderUploadStatus,
    'id'
);

$connection->addForeignKey(
    $installer->getFkName(
        $documentFinancingType,
        'applicable_to_customer_group_id',
        $documentFinanceCustomerGroup,
        'id'
    ),
    $documentFinancingType,
    'applicable_to_customer_group_id',
    $documentFinanceCustomerGroup,
    'id'
);

$connection->addForeignKey(
    $installer->getFkName(
        $documentFinancingType,
        'finance_group_id',
        $documentFinancingGroup,
        'id'
    ),
    $documentFinancingType,
    'finance_group_id',
    $documentFinancingGroup,
    'id'
);

$connection->addForeignKey(
    $installer->getFkName(
        $documentFinancingType,
        'document_type_id',
        $documentTypeTable,
        'id'
    ),
    $documentFinancingType,
    'document_type_id',
    $documentTypeTable,
    'id'
);

$connection->addForeignKey(
    $installer->getFkName(
        $documentTable,
        'document_finance_type_id',
        $documentFinancingType,
        'id'
    ),
    $documentTable,
    'document_finance_type_id',
    $documentFinancingType,
    'id'
);

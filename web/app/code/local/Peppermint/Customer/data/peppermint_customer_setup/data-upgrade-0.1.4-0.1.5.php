<?php
/**
 * @category Peppermint
 * @package Peppermint_Customer
 * @author Craig Goodspeed <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

/** @var Mage_Core_Model_resource_Setup $this */
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();
$documentTable =  $installer->getTable('rockar_customer/documents');
$documentLookupTable = $installer->getTable('peppermint_customer/document_type');
$documentCustomerGroupTable = $installer->getTable('peppermint_customer/document_customer_group');
$documentFinanceGroupTable = $installer->getTable('peppermint_customer/document_finance_group');
$documentOrderUploadStatusTable = $installer->getTable('peppermint_customer/document_upload_status');
$documentFinancingTypeTable = $installer->getTable('peppermint_customer/document_finance_type');
$anIndividual = 'an Individual';
$aCorporateCompany = 'a Corporate Company';
$BMWFinancialServices = 'BMW Financial Services';
$other = 'Other';
//data to insert

$documentType = [
    [
        'name' => "South African ID, or Passport",
        'description' => 'Document to identify the applicant, answers the question who is applying?',
        'allow_multiple_uploads' => 0,
        'finance_document_type_id' => 139
    ],
    [
        'name' => "Driver’s License",
        'description' => "The applicant's drivers license.",
        'allow_multiple_uploads' => 0,
        'finance_document_type_id' => 138
    ],
    [
        'name' => "Bank statements (last 3 months)",
        'description' => "The applicant's last 3 months worth of bank statements",
        'allow_multiple_uploads' => 1,
        'finance_document_type_id' => 26
    ],
    [
        'name' => "Latest payslip",
        'description' => "The applicant's proof of how they will pay for finance requested.",
        'allow_multiple_uploads' => 0,
        'finance_document_type_id' => 29
    ],
    [
        'name' => "Proof of Residence (no older than 3 months)",
        'description' => "The applicant's proof of how they will pay for finance requested.",
        'allow_multiple_uploads' => 0,
        'finance_document_type_id' => 140
    ],
    [
        'name' => "Company registration/CK documents",
        'description' => "Company's registration documents or CK documents.",
        'allow_multiple_uploads' => 0,
        'finance_document_type_id' => 27
    ],
    [
        'name' => "Company’s Latest Financial Statements",
        'description' => "Company’s Latest Financial Statements",
        'allow_multiple_uploads' => 1,
        'finance_document_type_id' => 28
    ],
    [
        'name' => "Company’s Latest 3 months Bank Statements",
        'description' => "Company’s Latest Financial Statements",
        'allow_multiple_uploads' => 1,
        'finance_document_type_id' => 26
    ],
    [
        'name' =>"Surety Proof of Residence (no older than 3 months)",
        'description' => "Company’s Latest Financial Statements",
        'allow_multiple_uploads' => 1,
        'finance_document_type_id' => 341
    ],
    [
        'name' =>"Proof of Business address",
        'description' => "Company’s Latest Financial Statements",
        'allow_multiple_uploads' => 0,
        'finance_document_type_id' => 141
    ],
    [
        'name' =>"Surety ID/Passport",
        'description' => "Company’s Latest Financial Statements",
        'allow_multiple_uploads' => 0,
        'finance_document_type_id' => 139
    ],
    [
        'name' =>"Surety Drivers Licence",
        'description' => "Company’s Latest Financial Statements",
        'allow_multiple_uploads' => 0,
        'finance_document_type_id' => 138
    ]
];

$documentCustomerGroup = [
    [
        'name' => $anIndividual,
        'description' => 'documents applicable to customers in their personal capacity.',
        'is_company' => 0
    ],
    [
        'name' => $aCorporateCompany,
        'description' => 'documents applicable to customers purchasing vehicles on behalf of a business.',
        'is_company' => 1
    ]
];

$documentFinanceGroup = [
    [
        'name' => $BMWFinancialServices,
        'description' => 'Documents required when applying for finance',
        'is_pay_in_full' => 0
    ],
    [
        'name' => $other,
        'description' => 'Documents required when purchasing a vehicle through another finance institution or using cash',
        'is_pay_in_full' => 1
    ]
];

$documentOrderUploadStatus = [
    [
        'name' => 'Completed',
        'description' => 'document has been uploaded and we have confirmation from imaging',
        'uploaded' => '1'
    ],
    [
        'name' => 'Processing',
        'description' => 'loaded and in process of uploading the document',
        'uploaded' => '0'
    ],
    [
        'name' => 'NotStarted',
        'description' => 'Document upload has not started yet, waiting for the cron interval to start',
        'uploaded' => '0'
    ],
    [
        'name' => 'Error',
        'description' => 'In error state this should require some input from a user, help me state.',
        'uploaded' => '0'
    ]
];

$documentFinancingType = [
    ['document_type_name' => 'South African ID, or Passport', 'applicable_to_customer_group_name' => $anIndividual, 'document_finance_group_name' => $other],
    ['document_type_name' => 'Driver’s License', 'applicable_to_customer_group_name' => $anIndividual, 'document_finance_group_name' => $other],
    ['document_type_name' => 'Proof of Residence (no older than 3 months)', 'applicable_to_customer_group_name' => $anIndividual, 'document_finance_group_name' => $other],
    ['document_type_name' => 'South African ID, or Passport', 'applicable_to_customer_group_name' => $anIndividual, 'document_finance_group_name' => $BMWFinancialServices],
    ['document_type_name' => 'Driver’s License', 'applicable_to_customer_group_name' => $anIndividual, 'document_finance_group_name' => $BMWFinancialServices],
    ['document_type_name' => 'Bank statements (last 3 months)', 'applicable_to_customer_group_name' => $anIndividual, 'document_finance_group_name' => $BMWFinancialServices],
    ['document_type_name' => 'Latest payslip', 'applicable_to_customer_group_name' => $anIndividual, 'document_finance_group_name' => $BMWFinancialServices],
    ['document_type_name' => 'Proof of Residence (no older than 3 months)', 'applicable_to_customer_group_name' => $anIndividual, 'document_finance_group_name' => $BMWFinancialServices],
    ['document_type_name' => 'Company registration/CK documents', 'applicable_to_customer_group_name' => $aCorporateCompany, 'document_finance_group_name' => $other],
    ['document_type_name' => 'Surety Proof of Residence (no older than 3 months)', 'applicable_to_customer_group_name' => $aCorporateCompany, 'document_finance_group_name' => $other],
    ['document_type_name' => 'Proof of Business address', 'applicable_to_customer_group_name' => $aCorporateCompany, 'document_finance_group_name' => $other],
    ['document_type_name' => 'Surety ID/Passport', 'applicable_to_customer_group_name' => $aCorporateCompany, 'document_finance_group_name' => $other],
    ['document_type_name' => 'Surety Drivers Licence', 'applicable_to_customer_group_name' => $aCorporateCompany, 'document_finance_group_name' => $other],
    ['document_type_name' => 'Company’s Latest Financial Statements', 'applicable_to_customer_group_name' => $aCorporateCompany, 'document_finance_group_name' => $BMWFinancialServices],
    ['document_type_name' => 'Company’s Latest 3 months Bank Statements', 'applicable_to_customer_group_name' => $aCorporateCompany, 'document_finance_group_name' => $BMWFinancialServices],
    ['document_type_name' => 'Surety Proof of Residence (no older than 3 months)', 'applicable_to_customer_group_name' => $aCorporateCompany, 'document_finance_group_name' => $BMWFinancialServices],
    ['document_type_name' => 'Proof of Business address', 'applicable_to_customer_group_name' => $aCorporateCompany, 'document_finance_group_name' => $BMWFinancialServices],
    ['document_type_name' => 'Surety ID/Passport', 'applicable_to_customer_group_name' => $aCorporateCompany, 'document_finance_group_name' => $BMWFinancialServices],
    ['document_type_name' => 'Surety Drivers Licence', 'applicable_to_customer_group_name' => $aCorporateCompany, 'document_finance_group_name' => $BMWFinancialServices]
];

function doLookupInserts($connection, $table, $collection) {
    foreach ($collection as $insertMe) {
        $connection->insert(
            $table,
            $insertMe
        );
    }
}

$connection->truncateTable($documentLookupTable);
$connection->truncateTable($documentCustomerGroupTable);
$connection->truncateTable($documentFinanceGroupTable);
$connection->truncateTable($documentOrderUploadStatusTable);

$connection->beginTransaction();
doLookupInserts($connection, $documentLookupTable, $documentType);
doLookupInserts($connection, $documentCustomerGroupTable, $documentCustomerGroup);
doLookupInserts($connection, $documentFinanceGroupTable, $documentFinanceGroup);
doLookupInserts($connection, $documentOrderUploadStatusTable, $documentOrderUploadStatus);

$customerGroupMap = Mage::getModel('peppermint_customer/document_customer_group')->getCollection()->getLookupCache();
$financeGroupMap = Mage::getModel('peppermint_customer/document_finance_group')->getCollection()->getLookupCache();
$documentTypeMap = Mage::getModel('peppermint_customer/document_type')->getCollection()->getLookupCache();

foreach ($documentFinancingType as $financeType) {
    $connection->insert(
        $documentFinancingTypeTable,
        [
            'finance_group_id' => $financeGroupMap[$financeType['document_finance_group_name']]->getId(),
            'document_type_id' => $documentTypeMap[$financeType['document_type_name']]->getId(),
            'applicable_to_customer_group_id' => $customerGroupMap[$financeType['applicable_to_customer_group_name']]->getId()
        ]
    );
}
$connection->commit();

$installer->endSetup();
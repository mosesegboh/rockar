<?php
/**
 * @category  Peppermint
 * @package   Peppermint_NewJourney
 * @author    Andrian Kogoshvili <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();

$documentLookupTable = $installer->getTable('peppermint_customer/document_type');
$documentFinancingTypeTable = $installer->getTable('peppermint_customer/document_finance_type');

$anIndividual = 'an Individual';
$BMWFinancialServices = 'BMW Financial Services';

$documentType = [
    [
        'name' => "Bank statement (1 month old)",
        'description' => "The applicant's 1 month old bank statement",
        'allow_multiple_uploads' => 0,
        'finance_document_type_id' => 26
    ],
    [
        'name' => "Bank statement (2 months old)",
        'description' => "The applicant's 2 month old bank statement",
        'allow_multiple_uploads' => 0,
        'finance_document_type_id' => 26
    ],
    [
        'name' => "Bank statement (3 months old)",
        'description' => "The applicant's 3 month old bank statement",
        'allow_multiple_uploads' => 0,
        'finance_document_type_id' => 26
    ]
];

$documentFinancingType = [
    [
        'document_type_name' => 'Bank statement (1 month old)',
        'applicable_to_customer_group_name' => $anIndividual,
        'document_finance_group_name' => $BMWFinancialServices
    ],
    [
        'document_type_name' => 'Bank statement (2 months old)',
        'applicable_to_customer_group_name' => $anIndividual,
        'document_finance_group_name' => $BMWFinancialServices
    ],
    [
        'document_type_name' => 'Bank statement (3 months old)',
        'applicable_to_customer_group_name' => $anIndividual,
        'document_finance_group_name' => $BMWFinancialServices
    ]
];


$connection->beginTransaction();

foreach ($documentType as $insertMe) {
    $connection->insert(
        $documentLookupTable,
        $insertMe
    );
}

// Delete redundant
$connection->delete(
    $documentLookupTable,
    ['name = ?' => 'Bank statements (last 3 months)']
);

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

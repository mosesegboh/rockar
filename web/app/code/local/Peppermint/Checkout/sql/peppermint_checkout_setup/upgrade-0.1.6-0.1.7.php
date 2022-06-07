<?php
/**
* @category  Peppermint
* @package   Peppermint\Checkout
* @author    Dumitru Mocanu <dumitru.mocanu@rockar.com>
* @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
*/

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$connection = $installer->getConnection();

$installer->startSetup();

$tableNames = [
    $installer->getTable('rockar_checkout/quote_additional'),
    $installer->getTable('rockar_checkout/order_additional')
];

$columnsToAdd = [
    'monthly_gross_salary' => [
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'precision' => 15,
        'scale' => 2,
        'nullable' => false,
        'default' => '0',
        'comment' => 'Monthly gross salary'
    ],
    'average_of_three_months_salary' => [
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'precision' => 15,
        'scale' => 2,
        'nullable' => false,
        'default' => '0',
        'comment' => 'Average of three months salary'
    ],
    'car_allowance' => [
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'precision' => 15,
        'scale' => 2,
        'nullable' => false,
        'default' => '0',
        'comment' => 'Car allowance'
    ],
    'take_home_salary' => [
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'precision' => 15,
        'scale' => 2,
        'nullable' => false,
        'default' => '0',
        'comment' => 'Take home salary'
    ],
    'additional_income' => [
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'precision' => 15,
        'scale' => 2,
        'nullable' => false,
        'default' => '0',
        'comment' => 'Additional income'
    ],
    'total_monthly_income' => [
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'precision' => 15,
        'scale' => 2,
        'nullable' => false,
        'default' => '0',
        'comment' => 'Total monthly income'
    ],
    'bond_rent_payment' => [
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'precision' => 15,
        'scale' => 2,
        'nullable' => false,
        'default' => '0',
        'comment' => 'Bond rent payment'
    ],
    'vehicle_installments' => [
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'precision' => 15,
        'scale' => 2,
        'nullable' => false,
        'default' => '0',
        'comment' => 'Vehicle installments'
    ],
    'credit_card_repayments' => [
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'precision' => 15,
        'scale' => 2,
        'nullable' => false,
        'default' => '0',
        'comment' => 'Credit card repayments'
    ],
    'clothing_accounts' => [
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'precision' => 15,
        'scale' => 2,
        'nullable' => false,
        'default' => '0',
        'comment' => 'Clothing accounts'
    ],
    'policy_repayments' => [
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'precision' => 15,
        'scale' => 2,
        'nullable' => false,
        'default' => '0',
        'comment' => 'Policy repayments'
    ],
    'transport_cost' => [
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'precision' => 15,
        'scale' => 2,
        'nullable' => false,
        'default' => '0',
        'comment' => 'Transport cost'
    ],
    'education_cost' => [
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'precision' => 15,
        'scale' => 2,
        'nullable' => false,
        'default' => '0',
        'comment' => 'Education cost'
    ],
    'household_expenses' => [
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'precision' => 15,
        'scale' => 2,
        'nullable' => false,
        'default' => '0',
        'comment' => 'Household expenses'
    ],
    'water_electricity_expenses' => [
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'precision' => 15,
        'scale' => 2,
        'nullable' => false,
        'default' => '0',
        'comment' => 'Water electricity expenses'
    ],
    'personal_loan_repayment' => [
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'precision' => 15,
        'scale' => 2,
        'nullable' => false,
        'default' => '0',
        'comment' => 'Personal loan repayment'
    ],
    'furniture_accounts' => [
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'precision' => 15,
        'scale' => 2,
        'nullable' => false,
        'default' => '0',
        'comment' => 'Furniture accounts'
    ],
    'over_draft_repayments' => [
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'precision' => 15,
        'scale' => 2,
        'nullable' => false,
        'default' => '0',
        'comment' => 'Over draft repayments'
    ],
    'telephone_payments' => [
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'precision' => 15,
        'scale' => 2,
        'nullable' => false,
        'default' => '0',
        'comment' => 'Telephone payments'
    ],
    'food_and_entertainment' => [
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'precision' => 15,
        'scale' => 2,
        'nullable' => false,
        'default' => '0',
        'comment' => 'Food and entertainment'
    ],
    'maintenance_expenses' => [
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'precision' => 15,
        'scale' => 2,
        'nullable' => false,
        'default' => '0',
        'comment' => 'Maintenance expenses'
    ],
    'other_expenses' => [
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'precision' => 15,
        'scale' => 2,
        'nullable' => false,
        'default' => '0',
        'comment' => 'Other expenses'
    ],
    'rent_amount' => [
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'precision' => 15,
        'scale' => 2,
        'nullable' => false,
        'default' => '0',
        'comment' => 'Rent amount'
    ],
    'medical_expenses' => [
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'precision' => 15,
        'scale' => 2,
        'nullable' => false,
        'default' => '0',
        'comment' => 'Medical expenses'
    ],
    'souce_of_additional_income' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 100,
        'nullable' => false,
        'default' => '',
        'comment' => 'Souce of additional income'
    ],
    'liable_as_surety' => [
        'type' => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'nullable' => false,
        'default' => '0',
        'comment' => 'Liable as surety'
    ],
    'liable_as_gaurantor' => [
        'type' => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'nullable' => false,
        'default' => '0',
        'comment' => 'Liable as gaurantor'
    ],
    'liable_as_co_debtor' => [
        'type' => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'nullable' => false,
        'default' => '0',
        'comment' => 'Liable as codebtor'
    ],
    'liable_as_comments' => [
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 100,
        'nullable' => false,
        'default' => '',
        'comment' => 'Liability as comments'
    ]
];

foreach ($tableNames as $tableName) {
    foreach ($columnsToAdd as $column => $dataType) {
        if (!$connection->tableColumnExists($tableName, $column)) {
            $connection->addColumn($tableName, $column, $dataType);
        }
    }
}

$installer->endSetup();

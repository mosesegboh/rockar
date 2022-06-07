<?php
/**
 * @category  Rockar
 * @package   Rockar/FinancingOptions
 * @author    Jiraphong Witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

/**
 * Finance variables to add
 */
$variables = [
    'interest_rate' => [
        'variable' => 'interest_rate',
        'variable_title' => 'Interest Rate',
        'calculation' => '${calc.interest_rate}',
        'type' => 2
    ],
    'individual_fee_capitalised' => [
        'variable' => 'individual_fee_capitalised',
        'variable_title' => 'Individual Fee Capitalised',
        'calculation' => '${calc.individual_fee_capitalised}',
        'type' => 1
    ],
    'individual_fee_monthly' => [
        'variable' => 'individual_fee_monthly',
        'variable_title' => 'Individual Fee Monthly',
        'calculation' => '${calc.individual_fee_monthly}',
        'type' => 1
    ]
];

foreach ($variables as $key => $data) {
    $variableModel = Mage::getModel('rockar_financingoptions/variables')
        ->load($key, 'variable');
    $variableModel->setData(array_merge($variableModel->getData(), $data))
        ->save();
}

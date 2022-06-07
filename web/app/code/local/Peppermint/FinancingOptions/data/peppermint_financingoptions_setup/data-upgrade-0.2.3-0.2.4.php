<?php
/**
 * @category  Setup
 * @package   Peppermint_FinancingOptions
 * @author    Mircea Chetan <mircea.chetan@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

$variables = [
    'individual_fee_capitalised' => [
        'variable' => 'initiation_fee',
        'variable_title' => 'Individual Fee Capitalised',
        'calculation' => '${calc.individual_fee_capitalised}',
        'type' => 1
    ],
    'individual_fee_monthly' => [
        'variable' => 'monthly_service_fee',
        'variable_title' => 'Individual Fee Monthly',
        'calculation' => '${calc.individual_fee_monthly}',
        'type' => 1
    ]
];

foreach ($variables as $key => $data) {
    $variableModel = Mage::getModel('rockar_financingoptions/variables')->load($key, 'variable');
    if ($variableModel->getId()) {
        $variableModel->addData($data)
            ->save();
    }
}

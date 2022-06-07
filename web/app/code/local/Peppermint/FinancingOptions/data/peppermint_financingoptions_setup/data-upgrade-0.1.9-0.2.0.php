<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Catalin Lungu <catalin.lungu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

// Finance variables to add
$variables = [
    'shortfall_applied' => [
        'variable' => 'shortfall_applied',
        'variable_title' => 'Shortfall Applied',
        'calculation' => '${calc.shortfall_applied}',
        'type' => 1
    ],
    'shortfall_support' => [
        'variable' => 'shortfall_support',
        'variable_title' => 'Shortfall Support',
        'calculation' => '${calc.shortfall_support}',
        'type' => 1
    ]
];

foreach ($variables as $key => $data) {
    $variableModel = Mage::getModel('rockar_financingoptions/variables')->load($key, 'variable');
    $variableModel->setData(array_merge($variableModel->getData(), $data))
        ->save();
}

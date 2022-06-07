<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */
 
// Finance variables to add

$variables = [
    'balloon_amount' => [
        'variable' => 'balloon_amount',
        'variable_title' => 'Balloon Amount',
        'calculation' => '${calc.balloon_amount}',
        'type' => 1
    ]
];

foreach ($variables as $key => $data) {
    $variableModel = Mage::getModel('rockar_financingoptions/variables')->load($key, 'variable');
    $variableModel->setData(array_merge($variableModel->getData(), $data))
        ->save();
}

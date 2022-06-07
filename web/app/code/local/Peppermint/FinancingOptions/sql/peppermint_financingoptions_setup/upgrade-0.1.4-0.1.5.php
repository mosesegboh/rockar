<?php

/**
 * @category     Setup
 * @package      Peppermint_Setup
 * @author       Catalin Lungu <catalin.lungu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

/** @var Array $variables - Variables to be inserted */
$variables = [
    [
        'frontend_title' => 'Trade-in Settlement Due',
        'body' =>
        [
            'variable' => 'px_settlement_payment',
            'variable_title' => 'Negative Equity to be paid in a lump-sum',
            'calculation' => '${calc.px_settlement_payment}',
            'type' => 1,
            'value_suffix' => '',
            'dependency' => 'px_settlement_payment',
            'show_edit_link' => 0,
            'css_classes' => '',
            'show_edit_icon' => 0
        ]
    ],
    [
        'frontend_title' => '{{ getWebsiteCode() }} Trade Assist Loan',
        'body' =>
        [
            'variable' => 'px_settlement_creditamount',
            'variable_title' => 'Negative Equity to be paid in a credit',
            'calculation' => '${calc.px_settlement_creditamount}',
            'type' => 1,
            'value_suffix' => '',
            'dependency' => 'px_settlement_creditamount',
            'show_edit_link' => 0,
            'css_classes' => '',
            'show_edit_icon' => 0
        ]
    ],
];

// Get all finance products
$financeProductsIds = Mage::getModel('rockar_financingoptions/options')->getCollection()
    ->addFieldToSelect('options_id')
    ->getData();
foreach ($variables as $variable) {
    $body = $variable['body'];

    // Check if variable already exists
    $var = Mage::getModel('rockar_financingoptions/variables')->load($body['variable'], 'variable');
    if (!$var->getId()) {
        // Variable does not exists so we can insert it
        $var->addData($body)
            ->save();
        $lastInsertedVariableId = $var->getId();

        foreach ($financeProductsIds as $financeProductId) {
            // Prepare data to insert
            $variableOptions = [
                'option_id' => $financeProductId['options_id'],
                'variable_id' => $lastInsertedVariableId,
                'variable_title' => $variable['frontend_title'],
                'sort_order' => ''
            ];
            Mage::getModel('peppermint_financingoptions/options_variables')->addData($variableOptions)
                ->save();
            Mage::getModel('peppermint_financingoptions/options_pdp_variables')->addData($variableOptions)
                ->save();
        }
    }
}

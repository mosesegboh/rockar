<?php
/**
 * @category     Peppermint
 * @package      Peppermint_FinancingOptions
 * @author       Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright    Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;

$installer->startSetup();

// add 2 new finance variables 'trade_in_settlement_due' and 'trade_in_surplus'
$financeVariables = [
    [
        'frontend_title' => 'Trade In Settlement Due',
        'body' =>
        [
            'variable' => 'trade_in_settlement_due',
            'variable_title' => 'Trade In Settlement Due',
            'calculation' => '${calc.trade_in_settlement_due}',
            'type' => 1,
            'value_suffix' => '',
            'dependency' => 'trade_in_settlement_due',
            'show_edit_link' => 0,
            'css_classes' => '',
            'show_edit_icon' => 0
        ]
    ],
    [
        'frontend_title' => 'Trade In Surplus',
        'body' =>
        [
            'variable' => 'trade_in_surplus',
            'variable_title' => 'Trade In Surplus',
            'calculation' => '${calc.trade_in_surplus}',
            'type' => 1,
            'value_suffix' => '',
            'dependency' => 'trade_in_surplus',
            'show_edit_link' => 0,
            'css_classes' => '',
            'show_edit_icon' => 0
        ]
    ]
];

$financeProductsIds = Mage::getModel('rockar_financingoptions/options')->getCollection()
    ->addFieldToSelect('options_id')
    ->addFieldToFilter('type', [
        'of1',
        'cashb',
        'cashmini',
        'cashm',
        'cashmoto',
        'cashc',
        'cashbmw'
    ])
    ->getData();

foreach ($financeVariables as $financeVariableData) {
    $body = $financeVariableData['body'];
    $financeVariable = Mage::getModel('rockar_financingoptions/variables')->load($body['variable'], 'variable');

    if (!$financeVariable->getId()) {
        $financeVariable->addData($body)
            ->save();

        foreach ($financeProductsIds as $financeProductId) {
            $variableOptions = [
                'option_id' => $financeProductId['options_id'],
                'variable_id' => $financeVariable->getId(),
                'variable_title' => $financeVariableData['frontend_title'],
                'sort_order' => ''
            ];
            Mage::getModel('peppermint_financingoptions/options_variables')->addData($variableOptions)
                ->save();
            Mage::getModel('peppermint_financingoptions/options_pdp_variables')->addData($variableOptions)
                ->save();
        }
    }
}

// update finance variables sort_order
$financeVariablesSortOrder = [
    'part_exchange' => 10,
    'outstanding_finance' => 20,
    'trade_in_settlement_due' => 30,
    'trade_in_surplus' => 40,
    'rockar_discount' => 50,
    'manufacture_savings' => 60,
    'cash_price' => 70,
    'coupon_discount' => 80
];

$financeVariablesToUpdate = Mage::getModel('rockar_financingoptions/variables')->getCollection()
    ->addFieldToSelect(['variable_id', 'variable'])
    ->addFieldToFilter('variable', array_keys($financeVariablesSortOrder))
    ->getData();

$financeVariablesToUpdateIds = [];
$financeVariablesToUpdateSortOrder = [];

foreach ($financeVariablesToUpdate as [ 'variable_id' => $variableId, 'variable' => $variable ]) {
    $financeVariablesToUpdateIds[] = $variableId;
    $financeVariablesToUpdateSortOrder[$variableId] = $financeVariablesSortOrder[$variable];
}

$productFinanceModel = Mage::getModel('rockar_financingoptions/options');

foreach ($financeProductsIds as $financeProductId) {
    if ($productFinanceModel->load($financeProductId['options_id'], 'options_id')->getId()) {
        $optionVariables = Mage::getModel('peppermint_financingoptions/options_variables')->getCollection()
            ->addFieldToFilter('option_id', $financeProductId['options_id'])
            ->addFieldToFilter('variable_id', $financeVariablesToUpdateIds)
            ->getData();

        $newVariablesFO = [];

        foreach ($optionVariables as $key => $value) {
            $newVariablesFO[$value['variable_id']] = $value;
            $newVariablesFO[$value['variable_id']]['sort_order'] = $financeVariablesToUpdateSortOrder[$value['variable_id']];
        }

        $productFinanceModel->setData('variables_link_data', $newVariablesFO);

        $optionPdpVariables = Mage::getModel('peppermint_financingoptions/options_pdp_variables')->getCollection()
            ->addFieldToFilter('option_id', $financeProductId['options_id'])
            ->addFieldToFilter('variable_id', $financeVariablesToUpdateIds)
            ->getData();

        $newVariablesFQ = [];

        foreach ($optionPdpVariables as $key => $value) {
            $newVariablesFQ[$value['variable_id']] = $value;
            $newVariablesFQ[$value['variable_id']]['sort_order'] = $financeVariablesToUpdateSortOrder[$value['variable_id']];
        }

        $productFinanceModel->setData('pdp_variables_link_data', $newVariablesFQ)
            ->save()
            ->unsetData();
    }
}

$installer->endSetup();

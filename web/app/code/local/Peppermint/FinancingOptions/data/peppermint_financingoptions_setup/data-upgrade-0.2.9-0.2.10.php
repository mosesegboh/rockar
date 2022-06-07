<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Jiraphong Witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();

$variableToRemove = 'trade_in_settlement_due';
$variableToAdd = [
    'variable' => 'px_settlement_payment',
    'data' => [
        'variable' => 'px_settlement_payment',
        'variable_title' => 'Trade-in Settlement Due',
        'calculation' => '${calc.px_settlement_payment}',
        'type' => 1,
        'value_suffix' => null,
        'dependency' => 'px_settlement_payment',
        'show_edit_link' => 0,
        'css_classes' => null,
        'show_edit_icon' => 0
    ]
];

$variableModel = Mage::getModel('rockar_financingoptions/variables');

// Adding variable if doesn't exist
$financeVariable = $variableModel->load($variableToAdd['variable'], 'variable');

if (!$financeVariable->getId()) {
    $financeVariable->addData($variableToAdd['data'])
        ->save();
}

$variableId = $financeVariable->getId();
$variableModel->unsetData();

// Getting variable to remove id
$variableToRemoveId = $variableModel->load($variableToRemove, 'variable')
    ->getId();

$cashFinanceProductsIds = Mage::getModel('rockar_financingoptions/options')->getCollection()
    ->addFieldToSelect('options_id')
    ->addFieldToFilter('type', [
        'cashbmw',
        'cashb',
        'cashmini',
        'cashm',
        'cashmoto',
        'cashc'
    ])
    ->getData();

$optionsVariblesModel = Mage::getModel('peppermint_financingoptions/options_variables');
$pdpVariblesModel =  Mage::getModel('peppermint_financingoptions/options_pdp_variables');

foreach ($cashFinanceProductsIds as $cashProduct) {
    // Removing variable overlay
    $associatedVariable = $optionsVariblesModel->getCollection()
        ->addFieldToFilter('option_id', $cashProduct['options_id'])
        ->addFieldToFilter('variable_id', $variableToRemoveId);

    if ($associatedVariable->getSize() === 1) {
        $associatedVariable->getFirstItem()
            ->delete();
    }
    // Removing variable pdp
    $associatedPdpVariable = $pdpVariblesModel->getCollection()
        ->addFieldToFilter('option_id', $cashProduct['options_id'])
        ->addFieldToFilter('variable_id', $variableToRemoveId);

    if ($associatedPdpVariable->getSize() === 1) {
        $associatedPdpVariable->getFirstItem()
            ->delete();
    }

    $variableOptions = [
        'option_id' => $cashProduct['options_id'],
        'variable_id' => $variableId,
        'variable_title' => $variableToAdd['data']['variable_title'],
        'sort_order' => '90'
    ];

    // Adding new variable to finance products overlay
    $associatedVariable = $optionsVariblesModel->getCollection()
        ->addFieldToFilter('option_id', $cashProduct['options_id'])
        ->addFieldToFilter('variable_id', $variableId);

    if ($associatedVariable->getSize() < 1) {
        $optionsVariblesModel->addData($variableOptions)
            ->save();
    }
    // Adding new variable to finance products pdp
    $associatedPdpVariable = $pdpVariblesModel->getCollection()
        ->addFieldToFilter('option_id', $cashProduct['options_id'])
        ->addFieldToFilter('variable_id', $variableId);

    if ($associatedPdpVariable->getSize() < 1) {
        $pdpVariblesModel->addData($variableOptions)
            ->save();
    }

    $optionsVariblesModel->unsetData();
    $pdpVariblesModel->unsetData();
}

$installer->endSetup();

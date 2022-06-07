<?php
/**
 * @category     Peppermint
 * @package      Peppermint_FinancingOptions
 * @author       Taras Kapushchak <techteam@rockar.com>
 * @copyright    Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;

$installer->startSetup();

// add 'shortfall_applied' finance variable to the 'Other Finance' options
$variableCode = 'shortfall_applied';
$variableTitle = 'Trade-in Support';
$variableSortOrder = '90';

$financeProductsIds = Mage::getModel('rockar_financingoptions/options')->getCollection()
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

$financeVariable = Mage::getModel('rockar_financingoptions/variables')->load($variableCode, 'variable');
if ($variableId = $financeVariable->getId()) {
    foreach ($financeProductsIds as $financeProductId) {
        $variableOptions = [
            'option_id' => $financeProductId['options_id'],
            'variable_id' => $variableId,
            'variable_title' => $variableTitle,
            'sort_order' => $variableSortOrder
        ];

        $variables = Mage::getModel('peppermint_financingoptions/options_variables')->getCollection()
            ->addFieldToFilter('option_id', $financeProductId['options_id'])
            ->addFieldToFilter('variable_id', $variableId);

        if ($variables->getSize() < 1) {
            Mage::getModel('peppermint_financingoptions/options_variables')->addData($variableOptions)
                ->save();
        }

        $variables = Mage::getModel('peppermint_financingoptions/options_pdp_variables')->getCollection()
            ->addFieldToFilter('option_id', $financeProductId['options_id'])
            ->addFieldToFilter('variable_id', $variableId);

        if ($variables->getSize() < 1) {
            Mage::getModel('peppermint_financingoptions/options_pdp_variables')->addData($variableOptions)
                ->save();
        }
    }
}

$installer->endSetup();

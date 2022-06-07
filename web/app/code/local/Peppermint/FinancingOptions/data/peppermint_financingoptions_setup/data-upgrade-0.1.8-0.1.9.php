<?php
/**
 * @category     Setup
 * @package      Peppermint_FinancingOptions
 * @author       Catalin Lungu <catalin.lungu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

$variableModel = Mage::getModel('rockar_financingoptions/variables')->load('balloon_percentage', 'variable');
$variableModel->setData(array_merge(
    $variableModel->getData(),
    [
        'variable' => 'balloon_percentage',
        'variable_title' => 'Balloon Percentage',
        'calculation' => '0',
        'type' => 2
    ]
))->save();

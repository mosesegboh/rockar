<?php
/**
 * @category     Setup
 * @package      Peppermint_FinancingOptions
 * @author       Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

$variableModel = Mage::getModel('rockar_financingoptions/variables')->load('balloon_percentage', 'variable');
$variableModel->addData(
    [
        'variable' => 'balloon_percentage',
        'variable_title' => 'Balloon Percentage',
        'calculation' => '${calc.balloon_percentage}',
        'type' => 2
    ]
)->save();

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Adrian Pescar <adrian.pescar@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

/**
 * Finance variables to add
 */
$variableModel = Mage::getModel('rockar_financingoptions/variables')->load('rate_subvention_amount', 'variable');
$variableModel->addData(
    [
        'variable' => 'rate_subvention_amount',
        'variable_title' => 'Rate Subvention Amount',
        'calculation' => '${calc.rate_subvention_amount}',
        'type' => 1
    ]
)->save();

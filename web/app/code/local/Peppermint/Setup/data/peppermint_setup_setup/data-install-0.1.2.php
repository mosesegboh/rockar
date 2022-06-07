<?php

/**
 * @category     FinanceGroup
 * @package      Peppermint_FinanceGroup
 * @author       Stefan Lucaci <lucacistefan.alexandru@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

// Load helper to calculate ranges for slider
$helper = Mage::helper('peppermint_setup/calculateRanges');
// Prepare ranges for each slider
$monthlyRange = [
    // value to reach => increment by
    30000 => 1000,
    50000 => 5000,
    100000 => 10000
];
$depositRange = [
    50000 => 5000,
    100000 => 10000,
    500000 => 25000,
    1000000 => 50000,
    3400000 => 100000
];
$mileageRange = [
    95000 => 5000
];
// Calculate range for each datagiven set
$deposit = $helper->calculateRange(0, $depositRange);
$monthly = $helper->calculateRange(0, $monthlyRange);
$mileage = $helper->calculateRange(10000, $mileageRange);
// Prepare finance groups for database insert
$financeGroup = [
    'group_title' => 'BMW Select Finance',
    'group_full_title' => 'BMW Select Finance',
    'sort_order' => 10,
    'is_static' => 0,
    'group_description' => 'Finance available through BMW Financial Services (South Africa) (Pty) Ltd, an Authorised Financial Services (FSP 4623) and Registered Credit Provider (NCRCP2341). BMW Financial Services provides no warranty either implicitly or expressly, as to the accuracy of the information or estimated values supplied by you. All products are subject to qualifying criteria and credit approval where applicable. T&C’ s apply in respect of specific finance option.',
    'is_business' => 0,
    'deposit_slider_steps' => $deposit,
    'deposit_default_step' => '0',
    'term_slider_steps' => '13,19,25,31,37,43,49,54,56',
    'term_default_step' => '49',
    'mileage_slider_steps' => $mileage,
    'mileage_default_step' => '80000',
    'monthlypay_slider_steps' => $monthly,
    'monthlypay_default_step' => '25000',
    'monthlypay_accuracy_to' => '100000',
    'monthlypay_accuracy_after' => '500',
    'payinfull_step' => NULL,
    'payinfull_min' => NULL,
    'payinfull_max' => NULL,
    'deposit_multiple_slider_steps' => NULL,
    'deposit_multiple_default_step' => NULL,
    'group_confirmation_popup' => NULL,
    'group_confirmation_popup_full' => NULL,
    'group_checkout_terms_and_conditions' => NULL,
    'grouped_under' => NULL
];
Mage::getModel('rockar_financingoptions/group')
    ->setData($financeGroup)
    ->save();

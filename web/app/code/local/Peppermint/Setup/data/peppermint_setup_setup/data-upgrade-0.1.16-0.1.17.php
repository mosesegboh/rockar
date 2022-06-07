<?php

/**
 * @category     Setup
 * @package      Peppermint_Setup
 * @author       Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

$this->startSetup();

// Load helper to calculate ranges for slider
$helper = Mage::helper('peppermint_setup/calculateRanges');

/**
 * Next lines are used only for BMW Instalment Sale
 */
$monthlyRangeInstalmentSale = [
    // value to reach => increment by
    30000 => 1000,
    50000 => 5000,
    100000 => 10000
];
$depositRangeInstalmentSale = [
    50000 => 5000,
    100000 => 10000,
    500000 => 25000,
    1000000 => 50000,
    3400000 => 100000
];
// Calculate range for each datagiven set
$depositInstalmentSale = $helper->calculateRange(0, $depositRangeInstalmentSale);
$monthlyInstalmentSale = $helper->calculateRange(0, $monthlyRangeInstalmentSale);
$mileageInstalmentSale = $helper->calculateRange(10000, [95000 => 5000]);
$balloonSliderStepsInstalmentSale = $helper->calculateRange(0, [60 => 1]);

$financeGroups = [
    [
        'group_title' => 'CASH',
        'group_full_title' => 'Cash Purchase',
        'sort_order' => 90,
        'is_static' => 0,
        'group_description' => 'This option represents the cash price that you are willing to spend on your new vehicle. Once you have set the amount that you are willing to purchase for, we will assist in finding the vehicle that best fits your requirements. Once you have found your vehicle, you will be requested to checkout by accepting your OTP, including reading through the terms and conditions. At the point of checkout you will be requested to select your preferred delivery dealer. At the end of the checkout process the cash amount due will displayed together with the banking details of your delivery dealer to allow you to make payment.',
        'is_business' => 0,
        'deposit_slider_steps' => NULL,
        'deposit_default_step' => NULL,
        'term_slider_steps' => NULL,
        'term_default_step' => NULL,
        'mileage_slider_steps' => NULL,
        'mileage_default_step' => NULL,
        'monthlypay_slider_steps' => NULL,
        'monthlypay_default_step' => NULL,
        'monthlypay_accuracy_to' => NULL,
        'monthlypay_accuracy_after' => NULL,
        'payinfull_step' => NULL,
        'payinfull_min' => NULL,
        'payinfull_max' => NULL,
        'deposit_multiple_slider_steps' => NULL,
        'deposit_multiple_default_step' => NULL,
        'group_confirmation_popup' => NULL,
        'group_confirmation_popup_full' => NULL,
        'group_checkout_terms_and_conditions' => NULL,
        'grouped_under' => NULL
    ],
    [
        'group_title' => 'BMW Installment Sale',
        'group_full_title' => 'BMW Instalment Sale',
        'sort_order' => 20,
        'is_static' => 0,
        'group_description' => 'Finance available through BMW Financial Services (South Africa) (Pty) Ltd, an Authorised Financial Services (FSP 4623) and Registered Credit Provider (NCRCP2341). BMW Financial Services provides no warranty either implicitly or expressly, as to the accuracy of the information or estimated values supplied by you. All products are subject to qualifying criteria and credit approval where applicable. T&C’ s apply in respect of specific finance option.',
        'is_business' => 0,
        'deposit_slider_steps' => $depositInstalmentSale,
        'deposit_default_step' => '0',
        'term_slider_steps' => '13,19,25,31,37,43,49,54,56',
        'term_default_step' => '49',
        'mileage_slider_steps' => $mileageInstalmentSale,
        'mileage_default_step' => '80000',
        'monthlypay_slider_steps' => $monthlyInstalmentSale,
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
        'grouped_under' => NULL,
        'balloon_slider_steps' => $balloonSliderStepsInstalmentSale,
        'balloon_default_step' => '40'
    ]
];

foreach($financeGroups as $financeGroup) {
    $financeGroupId = Mage::getModel('rockar_financingoptions/group')->load($financeGroup["group_title"], 'group_title')
        ->getId();
    if (!$financeGroupId) {
        Mage::getModel('rockar_financingoptions/group')->setData($financeGroup)
            ->save();
    }
}

$this->endSetup();

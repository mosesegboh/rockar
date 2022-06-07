<?php
/**
 * @category     Setup
 * @package      Peppermint_Setup
 * @author       Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

$this->startSetup();

$helper = Mage::helper('peppermint_setup/calculateRanges');

$monthlyRangeInstalmentSale = [
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

$depositInstalmentSale = $helper->calculateRange(0, $depositRangeInstalmentSale);
$monthlyInstalmentSale = $helper->calculateRange(0, $monthlyRangeInstalmentSale);
$balloonSliderStepsInstalmentSale = $helper->calculateRange(0, [60 => 1]);

$financeGroup = [
    'group_title' => 'BMW Instalment Sale',
    'group_full_title' => 'BMW Instalment Sale',
    'sort_order' => 20,
    'is_static' => 0,
    'group_description' => 'Finance available through BMW Financial Services (South Africa) (Pty) Ltd, an Authorised Financial Services (FSP 4623) and Registered Credit Provider (NCRCP2341). BMW Financial Services provides no warranty either implicitly or expressly, as to the accuracy of the information or estimated values supplied by you. All products are subject to qualifying criteria and credit approval where applicable. T&C’ s apply in respect of specific finance option.',
    'is_business' => 0,
    'deposit_slider_steps' => $depositInstalmentSale,
    'deposit_default_step' => '0',
    'term_slider_steps' => '12,18,24,30,36,42,48,53,55,60,72',
    'term_default_step' => '60',
    'mileage_slider_steps' => NULL,
    'mileage_default_step' => NULL,
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
];

$var = Mage::getModel('rockar_financingoptions/group')->load($financeGroup["group_title"], 'group_title');
if ($var->getId()) {
    $var->addData($financeGroup)
        ->save();
}

$this->endSetup();

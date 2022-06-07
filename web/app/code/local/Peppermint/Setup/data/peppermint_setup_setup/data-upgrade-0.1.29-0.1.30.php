<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Setup
 * @author    Cristian Moga <cristian.moga@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();

// Load helper to calculate ranges for slider
$helper = Mage::helper('peppermint_setup/calculateRanges');
$financingOptionsGroupModel = Mage::getModel('rockar_financingoptions/group');

$financeGroups = [
    [
        'group_title' => 'BMW Select Finance',
        'deposit_slider_steps' => $helper->calculateRange(
            0,
            [
                50000 => 5000,
                100000 => 10000,
                500000 => 25000,
                1000000 => 50000,
                3400000 => 100000
            ]
        )
    ],
    [
        'group_title' => 'MINI Select Finance',
        'group_description' => '<p>Drive a new MINI every 3 years or just enjoy the benefits of tailored finance. Choose a mileage limit and number of instalments that suit you &ndash; and enjoy the perks of a Guaranteed Future Value (GFV) that shields you from depreciation. At the end of your contract you can either trade in for a newer model, settle (GFV as one final payment), refinance or return your MINI at no additional cost to you.</p><p>Available through MINI Financial Services, a division of BMW Financial Services (South Africa) (Pty) Ltd, an Authorised Financial Services (FSP 4623) and Registered Credit Provider (NCRCP2341). BMW Financial Services provides no warranty either implicitly or expressly, as to the accuracy of the information or estimated values supplied by you. All products are subject to qualifying criteria and credit approval where applicable. T&amp;C&#39;s apply in respect of specific finance option.</p>',
        'deposit_slider_steps' => $helper->calculateRange(
            0,
            [
                50000 => 5000,
                100000 => 10000,
                500000 => 25000,
                1000000 => 50000
            ]
        ),
        'deposit_default_step' => 0,
        'monthlypay_slider_steps' => $helper->calculateRange(
            0,
            [
                1000 => 500,
                30000 => 1000,
                50000 => 5000,
                100000 => 10000
            ]
        ),
        'monthlypay_default_step' => '25000',
        'mileage_default_step' => null
    ],
    [
        'group_title' => 'MINI Instalment Sale',
        'group_description' => '<p>Simple and sorted &ndash; the MINI Instalment Sale lets you set affordable monthly payments with full ownership of your MINI waiting at the finish line. Choose the deposit amount that&rsquo;s right for you and the option of adding a balloon payment which can either be settled or refinanced at the end of your contract.</p><p>Finance available through MINI Financial Services, a division of BMW Financial Services (South Africa) (Pty) Ltd, an Authorised Financial Services (FSP 4623) and Registered Credit Provider (NCRCP2341). BMW Financial Services provides no warranty either implicitly or expressly, as to the accuracy of the information or estimated values supplied by you. All products are subject to qualifying criteria and credit approval where applicable. T&amp;C&#39;s apply in respect of specific finance option.</p>',
        'deposit_slider_steps' => $helper->calculateRange(
            0,
            [
                50000 => 5000,
                100000 => 10000,
                500000 => 25000,
                1000000 => 50000
            ]
        ),
        'deposit_default_step' => 0,
        'term_slider_steps' => $helper->calculateRange(
            12,
            [
                48 => 6,
                53 => 5,
                55 => 2,
                60 => 5,
                72 => 12
            ]
        ),
        'monthlypay_default_step' => '25000'
    ],
    [
        'group_title' => 'BMW Motorrad Select Finance',
        'group_description' => '<p>Start a new adventure every 3 years or simply enjoy a tailored approach to your motorcycle finance. Choose a mileage limit and number of instalments that suit your budget &ndash; and enjoy the added advantage of having a Guaranteed Future Value (GFV) that shields you from depreciation. At the end of your contract you can either trade in for a newer model, settle (GFV as one final payment), refinance or return your motorcycle at no additional cost to you</p><p>Finance available through BMW Financial Services (South Africa) (Pty) Ltd, an Authorised Financial Services (FSP 4623) and Registered Credit Provider (NCRCP2341). BMW Financial Services provides no warranty either implicitly or expressly, as to the accuracy of the information or estimated values supplied by you. All products are subject to qualifying criteria and credit approval where applicable. T&amp;C&#39;s apply in respect of specific finance option.</p>',
        'deposit_slider_steps' => $helper->calculateRange(0, [400000 => 10000]),
        'deposit_default_step' => 0,
        'monthlypay_slider_steps' => $helper->calculateRange(0, [15000 => 500]),
        'monthlypay_default_step' => '10000',
        'monthlypay_accuracy_to' => '15000',
        'monthlypay_accuracy_from' => null,
        'mileage_slider_steps' => $helper->calculateRange(5000, [40000 => 5000]),
        'mileage_default_step' => '40000',
        'term_slider_steps' => $helper->calculateRange(
            12,
            [
                36 => 12,
                42 => 6
            ]
        ),
        'term_default_step' => '42'
    ],
    [
        'group_title' => 'BMW Motorrad Instalment Sale',
        'group_description' => '<p>Straightforward and simple &ndash; the BMW Motorrad Instalment Sale helps you tailor affordable monthly payments that see you taking ownership of your motorcycle at the end of your journey. Decide on the deposit amount that&rsquo;s right for you and enjoy the option of including a balloon payment which can either be settled or refinanced at the end of your contract term</p><p>Finance available through BMW Financial Services (South Africa) (Pty) Ltd, an Authorised Financial Services (FSP 4623) and Registered Credit Provider (NCRCP2341). BMW Financial Services provides no warranty either implicitly or expressly, as to the accuracy of the information or estimated values supplied by you. All products are subject to qualifying criteria and credit approval where applicable. T&amp;C&#39;s apply in respect of specific finance option.</p>',
        'deposit_slider_steps' => $helper->calculateRange(0, [400000 => 10000]),
        'deposit_default_step' => 0,
        'monthlypay_slider_steps' => $helper->calculateRange(0, [15000 => 500]),
        'monthlypay_default_step' => '10000',
        'monthlypay_accuracy_to' => '15000',
        'monthlypay_accuracy_from' => null,
        'balloon_slider_steps' => $helper->calculateRange(0, [20 => 1]),
        'balloon_default_step' => '0',
        'term_slider_steps' => $helper->calculateRange(
            12,
            [
                36 => 12,
                42 => 6,
                60 => 18,
                72 => 12
            ]
        ),
        'term_default_step' => '60',
        'payinfull_step' => null,
        'payinfull_min' => null,
        'payinfull_max' => null
    ]
];

$motorradFinanceGroup = [
    'group_title' => 'BMW Motorrad Select Finance',
    'group_full_title' => 'BMW Motorrad Select Finance',
    'grouped_under' => 'BMW Motorrad Select Finance'
];

$financingOptionsGroupModel->load('BMW Motorrad Select', 'group_title');

if ($financingOptionsGroupModel->getId()) {
    $financingOptionsGroupModel->addData($motorradFinanceGroup)
        ->save();

    $financingOptionsGroupModel->unsetData();
}

foreach ($financeGroups as $financeGroupData) {
    $financeGroup = $financingOptionsGroupModel->load($financeGroupData['group_title'], 'group_title');
    if ($financeGroup->getId()) {
        $financeGroup->addData($financeGroupData)
            ->save();
    }

    $financingOptionsGroupModel->unsetData();
}

$installer->endSetup();

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Setup
 * @author    Catalin Lungu <catalin.lungu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

$this->startSetup();
$helper = Mage::helper('peppermint_setup/calculateRanges');

$bmwFinanceGroups = [
    [
        'group_title' => 'BMW Select Finance',
        'sort_order' => 10,
        'is_static' => 0,
        'group_description' => '<p>Drive a new BMW every 3 years or simply enjoy a tailored approach to your vehicle finance. Choose a mileage limit and number of instalments that suit your budget &ndash; and enjoy the added advantage of having a Guaranteed Future Value (GFV) that shields you from depreciation. At the end of your contract you can either trade in your BMW for a newer model, settle (GFV as one final payment), refinance or return your vehicle at no additional cost to you.</p><p>Finance available through BMW Financial Services (South Africa) (Pty) Ltd, an Authorised Financial Services (FSP 4623) and Registered Credit Provider (NCRCP2341). BMW Financial Services provides no warranty either implicitly or expressly, as to the accuracy of the information or estimated values supplied by you. All products are subject to qualifying criteria and credit approval where applicable. T&amp;C&rsquo; s apply in respect of specific finance option.</p>',
        'is_business' => 0,
        'group_full_title' => 'BMW Select Finance',
        'deposit_slider_steps' => $helper->calculateRange(
            0,
            [
                1000 => 1000,
                5000 => 4000,
                50000 => 5000,
                100000 => 10000,
                500000 => 25000,
                1000000 => 50000,
                3400000 => 100000
            ]
        ),
        'deposit_default_step' => '0',
        'term_slider_steps' => '12,18,24,30,36,42,48,53,55',
        'term_default_step' => '48',
        'mileage_slider_steps' => $helper->calculateRange(
            10000,
            [
                95000 => 5000
            ]
        ),
        'mileage_default_step' => '80000',
        'monthlypay_slider_steps' => $helper->calculateRange(
            0,
            [
                30000 => 1000,
                50000 => 5000,
                100000 => 10000
            ]
        ),
        'monthlypay_default_step' => '25000',
        'monthlypay_accuracy_to' => '100000',
        'monthlypay_accuracy_after' => '500',
        'payinfull_step' => null,
        'payinfull_min' => null,
        'payinfull_max' => null,
        'deposit_multiple_slider_steps' => null,
        'deposit_multiple_default_step' => null,
        'group_confirmation_popup' => null,
        'group_confirmation_popup_full' => null,
        'group_checkout_terms_and_conditions' => null,
        'grouped_under' => 'BMW Select Finance',
        'balloon_slider_steps' => null,
        'balloon_default_step' => null
    ],
    [
        'group_title' => 'BMW Instalment Sale',
        'sort_order' => 20,
        'is_static' => 0,
        'group_description' => '<p>Finance available through BMW Financial Services (South Africa) (Pty) Ltd, an Authorised Financial Services (FSP 4623) and Registered Credit Provider (NCRCP2341). BMW Financial Services provides no warranty either implicitly or expressly, as to the accuracy of the information or estimated values supplied by you. All products are subject to qualifying criteria and credit approval where applicable. T&C’ s apply in respect of specific finance option.</p>',
        'is_business' => 0,
        'group_full_title' => 'BMW Instalment Sale',
        'deposit_slider_steps' => $helper->calculateRange(
            0,
            [
                50000 => 5000,
                100000 => 10000,
                500000 => 25000,
                1000000 => 50000,
                3400000 => 100000
            ]
        ),
        'deposit_default_step' => '0',
        'term_slider_steps' => '12,18,24,30,36,42,48,53,55,60,72',
        'term_default_step' => '60',
        'mileage_slider_steps' => null,
        'mileage_default_step' => null,
        'monthlypay_slider_steps' => $helper->calculateRange(
            0,
            [
                30000 => 1000,
                50000 => 5000,
                100000 => 10000
            ]
        ),
        'monthlypay_default_step' => '25000',
        'monthlypay_accuracy_to' => '100000',
        'monthlypay_accuracy_after' => '500',
        'payinfull_step' => null,
        'payinfull_min' => null,
        'payinfull_max' => null,
        'deposit_multiple_slider_steps' => null,
        'deposit_multiple_default_step' => null,
        'group_confirmation_popup' => null,
        'group_confirmation_popup_full' => null,
        'group_checkout_terms_and_conditions' => null,
        'grouped_under' => 'BMW Instalment Sale',
        'balloon_slider_steps' => $helper->calculateRange(0, [60 => 1]),
        'balloon_default_step' => '40'
    ],
    [
        'group_title' => 'Cash Purchase',
        'sort_order' => 30,
        'is_static' => 0,
        'group_description' => '<p>Ready. Set. Go. The cash option gives you the ability to self-fund your purchase upfront and without interest or the need for a credit check. Just set your desired total spend and we\'ll assist you in finding a BMW that suits your requirements. At checkout you have the option to accept an offer to purchase and arrange payment and delivery online with your chosen BMW Retailer.</p>',
        'is_business' => 0,
        'group_full_title' => 'Cash Purchase',
        'deposit_slider_steps' => $helper->calculateRange(
            0,
            [
                50000 => 5000,
                100000 => 10000,
                500000 => 25000,
                1000000 => 50000,
                3400000 => 100000
            ]
        ),
        'deposit_default_step' => null,
        'term_slider_steps' => null,
        'term_default_step' => null,
        'mileage_slider_steps' => null,
        'mileage_default_step' => null,
        'monthlypay_slider_steps' => null,
        'monthlypay_default_step' => null,
        'monthlypay_accuracy_to' => null,
        'monthlypay_accuracy_after' => null,
        'payinfull_step' => '50000',
        'payinfull_min' => '0',
        'payinfull_max' => '5000000',
        'deposit_multiple_slider_steps' => null,
        'deposit_multiple_default_step' => null,
        'group_confirmation_popup' => null,
        'group_confirmation_popup_full' => null,
        'group_checkout_terms_and_conditions' => null,
        'grouped_under' => 'Cash Purchase',
        'balloon_slider_steps' => null,
        'balloon_default_step' => null
    ],
    [
        'group_title' => 'BMW Other Finance',
        'sort_order' => 40,
        'is_static' => 0,
        'group_description' => '<p>If you choose to finance your BMW with another institution or bank, you can still use this platform to find your vehicle. Just estimate the total amount you\'re planning to pay and we\'ll assist you in finding a vehicle that suits your requirements.At the end of the process, you\'ll be able to checkout with a no-obligation offer to purchase which you can then present to either your bank or your preferred BMW Retailer to arrange finance</p>',
        'is_business' => 0,
        'group_full_title' => 'BMW Other Finance',
        'deposit_slider_steps' => $helper->calculateRange(
            0,
            [
                50000 => 5000,
                100000 => 10000,
                500000 => 25000,
                1000000 => 50000,
                3400000 => 100000
            ]
        ),
        'deposit_default_step' => null,
        'term_slider_steps' => null,
        'term_default_step' => null,
        'mileage_slider_steps' => null,
        'mileage_default_step' => null,
        'monthlypay_slider_steps' => null,
        'monthlypay_default_step' => null,
        'monthlypay_accuracy_to' => null,
        'monthlypay_accuracy_after' => null,
        'payinfull_step' => '10000',
        'payinfull_min' => '150000',
        'payinfull_max' => '5000000',
        'deposit_multiple_slider_steps' => null,
        'deposit_multiple_default_step' => null,
        'group_confirmation_popup' => null,
        'group_confirmation_popup_full' => null,
        'group_checkout_terms_and_conditions' => null,
        'grouped_under' => 'BMW Other Finance',
        'balloon_slider_steps' => null,
        'balloon_default_step' => null
    ]
];

$miniFinanceGroup = [
    [
        'group_title' => 'MINI Select Finance',
        'sort_order' => 110,
        'is_static' => 0,
        'group_description' => '<p>Drive a new MINI every 3 years or just enjoy the benefits of tailored finance. Choose a mileage limit and number of instalments that suit you &ndash; and enjoy the perks of a Guaranteed Future Value (GFV) that shields you from depreciation. At the end of your contract you can either trade in for a newer model, settle (GFV as one final payment), refinance or return your MINI at no additional cost to you.</p>',
        'is_business' => 0,
        'group_full_title' => 'MINI Select Finance',
        'deposit_slider_steps' => $helper->calculateRange(
            0,
            [
                1000 => 1000,
                5000 => 4000,
                50000 => 5000,
                100000 => 10000,
                500000 => 25000,
                1000000 => 50000,
                3400000 => 100000
            ]
        ),
        'deposit_default_step' => null,
        'term_slider_steps' => '12,18,24,30,36,42,48,53,55',
        'term_default_step' => '48',
        'mileage_slider_steps' => $helper->calculateRange(
            10000,
            [
                95000 => 5000
            ]
        ),
        'mileage_default_step' => null,
        'monthlypay_slider_steps' => $helper->calculateRange(
            0,
            [
                30000 => 1000,
                50000 => 5000,
                100000 => 10000
            ]
        ),
        'monthlypay_default_step' => null,
        'monthlypay_accuracy_to' => null,
        'monthlypay_accuracy_after' => null,
        'payinfull_step' => null,
        'payinfull_min' => null,
        'payinfull_max' => null,
        'deposit_multiple_slider_steps' => null,
        'deposit_multiple_default_step' => null,
        'group_confirmation_popup' => null,
        'group_confirmation_popup_full' => null,
        'group_checkout_terms_and_conditions' => null,
        'grouped_under' => 'MINI Select Finance',
        'balloon_slider_steps' => null,
        'balloon_default_step' => null
    ],
    [
        'group_title' => 'MINI Instalment Sale',
        'sort_order' => 120,
        'is_static' => 0,
        'group_description' => '<p>Simple and sorted &ndash; the MINI Instalment Sale lets you set affordable monthly payments with full ownership of your MINI waiting at the finish line. Choose the deposit amount that&rsquo;s right for you and the option of adding a balloon payment which can either be settled or refinanced at the end of your contract.</p>',
        'is_business' => 0,
        'group_full_title' => 'MINI Instalment Sale',
        'deposit_slider_steps' => null,
        'deposit_default_step' => null,
        'term_slider_steps' => '12,18,24,30,36,42,48,54,60,72',
        'term_default_step' => '60',
        'mileage_slider_steps' => null,
        'mileage_default_step' => null,
        'monthlypay_slider_steps' => $helper->calculateRange(
            0,
            [
                30000 => 1000,
                50000 => 5000,
                100000 => 10000
            ]
        ),
        'monthlypay_default_step' => null,
        'monthlypay_accuracy_to' => null,
        'monthlypay_accuracy_after' => null,
        'payinfull_step' => null,
        'payinfull_min' => null,
        'payinfull_max' => null,
        'deposit_multiple_slider_steps' => null,
        'deposit_multiple_default_step' => null,
        'group_confirmation_popup' => null,
        'group_confirmation_popup_full' => null,
        'group_checkout_terms_and_conditions' => null,
        'grouped_under' => 'MINI Instalment Sale',
        'balloon_slider_steps' => $helper->calculateRange(0, [60 => 1]),
        'balloon_default_step' => '40'
    ],
    [
        'group_title' => 'MINI Cash Purchase',
        'sort_order' => 130,
        'is_static' => 0,
        'group_description' => '<p>Ready. Set. Go. You\'ve got the cash - just decide the total amount you want to spend and we\'ll help find the perfect MINI for you.At checkout you can accept an offer to purchase, arrange payment and delivery online with your chosen MINI Retailer</p>',
        'is_business' => 0,
        'group_full_title' => 'MINI Cash Purchase',
        'deposit_slider_steps' => null,
        'deposit_default_step' => null,
        'term_slider_steps' => null,
        'term_default_step' => null,
        'mileage_slider_steps' => null,
        'mileage_default_step' => null,
        'monthlypay_slider_steps' => null,
        'monthlypay_default_step' => null,
        'monthlypay_accuracy_to' => null,
        'monthlypay_accuracy_after' => null,
        'payinfull_step' => '10000',
        'payinfull_min' => '150000',
        'payinfull_max' => '1500000',
        'deposit_multiple_slider_steps' => null,
        'deposit_multiple_default_step' => null,
        'group_confirmation_popup' => null,
        'group_confirmation_popup_full' => null,
        'group_checkout_terms_and_conditions' => null,
        'grouped_under' => 'MINI Cash Purchase',
        'balloon_slider_steps' => null,
        'balloon_default_step' => null
    ],
    [
        'group_title' => 'MINI Other Finance',
        'sort_order' => 140,
        'is_static' => 0,
        'group_description' => '<p>If you choose to finance your MINI with another institution or bank, you can still use this platform to find your MINI. Just estimate the total amount you\'re planning to pay and we\'ll help you match with your perfect MINI.At the end of the process, you\'ll be able to checkout with a no-obligation offer to purchase which you can then pass on to either your bank or your preferred MINI Retailer to arrange finance.</p>',
        'is_business' => 0,
        'group_full_title' => 'MINI Other Finance',
        'deposit_slider_steps' => null,
        'deposit_default_step' => null,
        'term_slider_steps' => null,
        'term_default_step' => null,
        'mileage_slider_steps' => null,
        'mileage_default_step' => null,
        'monthlypay_slider_steps' => null,
        'monthlypay_default_step' => null,
        'monthlypay_accuracy_to' => null,
        'monthlypay_accuracy_after' => null,
        'payinfull_step' => '10000',
        'payinfull_min' => '150000',
        'payinfull_max' => '5000000',
        'deposit_multiple_slider_steps' => null,
        'deposit_multiple_default_step' => null,
        'group_confirmation_popup' => null,
        'group_confirmation_popup_full' => null,
        'group_checkout_terms_and_conditions' => null,
        'grouped_under' => 'MINI Other Finance',
        'balloon_slider_steps' => null,
        'balloon_default_step' => null
    ]
];

$motorradFinanceGroup = [
    [
        'group_title' => 'BMW Motorrad Select',
        'sort_order' => 210,
        'is_static' => 0,
        'group_description' => '<p>Start a new adventure every 3 years or simply enjoy a tailored approach to your motorcycle finance. Choose a mileage limit and number of instalments that suit your budget &ndash; and enjoy the added advantage of having a Guaranteed Future Value (GFV) that shields you from depreciation. At the end of your contract you can either trade in for a newer model, settle (GFV as one final payment), refinance or return your motorcycle at no additional cost to you</p>',
        'is_business' => 0,
        'group_full_title' => 'BMW Motorrad Select',
        'deposit_slider_steps' => $helper->calculateRange(
            0,
            [
                1000 => 1000,
                5000 => 4000,
                50000 => 5000,
                100000 => 10000,
                500000 => 25000,
                1000000 => 50000,
                3400000 => 100000
            ]
        ),
        'deposit_default_step' => null,
        'term_slider_steps' => '12,18,24,30,36,42,48,53,55',
        'term_default_step' => null,
        'mileage_slider_steps' => $helper->calculateRange(
            10000,
            [
                95000 => 5000
            ]
        ),
        'mileage_default_step' => null,
        'monthlypay_slider_steps' => $helper->calculateRange(
            0,
            [
                30000 => 1000,
                50000 => 5000,
                100000 => 10000
            ]
        ),
        'monthlypay_default_step' => null,
        'monthlypay_accuracy_to' => null,
        'monthlypay_accuracy_after' => null,
        'payinfull_step' => null,
        'payinfull_min' => null,
        'payinfull_max' => null,
        'deposit_multiple_slider_steps' => null,
        'deposit_multiple_default_step' => null,
        'group_confirmation_popup' => null,
        'group_confirmation_popup_full' => null,
        'group_checkout_terms_and_conditions' => null,
        'grouped_under' => 'BMW Motorrad Select',
        'balloon_slider_steps' => null,
        'balloon_default_step' => null
    ],
    [
        'group_title' => 'BMW Motorrad Instalment Sale',
        'sort_order' => 220,
        'is_static' => 0,
        'group_description' => '<p>Straightforward and simple &ndash; the BMW Motorrad Instalment Sale helps you tailor affordable monthly payments that see you taking ownership of your motorcycle at the end of your journey. Decide on the deposit amount that&rsquo;s right for you and enjoy the option of including a balloon payment which can either be settled or refinanced at the end of your contract term</p>',
        'is_business' => 0,
        'group_full_title' => 'BMW Motorrad Instalment Sale',
        'deposit_slider_steps' => null,
        'deposit_default_step' => null,
        'term_slider_steps' => '12,18,24,30,36,42,48,54,60,72',
        'term_default_step' => '60',
        'mileage_slider_steps' => null,
        'mileage_default_step' => null,
        'monthlypay_slider_steps' => null,
        'monthlypay_default_step' => null,
        'monthlypay_accuracy_to' => null,
        'monthlypay_accuracy_after' => null,
        'payinfull_step' => '5000',
        'payinfull_min' => '150000',
        'payinfull_max' => '9999999',
        'deposit_multiple_slider_steps' => null,
        'deposit_multiple_default_step' => null,
        'group_confirmation_popup' => null,
        'group_confirmation_popup_full' => null,
        'group_checkout_terms_and_conditions' => null,
        'grouped_under' => 'BMW Motorrad Instalment Sale',
        'balloon_slider_steps' => $helper->calculateRange(0, [60 => 1]),
        'balloon_default_step' => '40'
    ],
    [
        'group_title' => 'BMW Motorrad Cash Purchase',
        'sort_order' => 230,
        'is_static' => 0,
        'group_description' => '<p>Ready. Set. Go. The cash option gives you the ability to self-fund your BMW Motorcycle rchase upfront and without interest or the need for a credit check. Just set your desired total spend and we\'ll assist you in finding the motorcycle that\'s right for you.At checkout you have the option to accept an offer to purchase and arrange payment and delivery online with your chosen BMW Motorrad Retailer.</p>',
        'is_business' => 0,
        'group_full_title' => 'BMW Motorrad Cash Purchase',
        'deposit_slider_steps' => null,
        'deposit_default_step' => null,
        'term_slider_steps' => null,
        'term_default_step' => null,
        'mileage_slider_steps' => null,
        'mileage_default_step' => null,
        'monthlypay_slider_steps' => null,
        'monthlypay_default_step' => null,
        'monthlypay_accuracy_to' => null,
        'monthlypay_accuracy_after' => null,
        'payinfull_step' => '10000',
        'payinfull_min' => '70000',
        'payinfull_max' => '1500000',
        'deposit_multiple_slider_steps' => null,
        'deposit_multiple_default_step' => null,
        'group_confirmation_popup' => null,
        'group_confirmation_popup_full' => null,
        'group_checkout_terms_and_conditions' => null,
        'grouped_under' => 'BMW Motorrad Cash Purchase',
        'balloon_slider_steps' => null,
        'balloon_default_step' => null
    ],
    [
        'group_title' => 'BMW Motorrad Other Finance',
        'sort_order' => 240,
        'is_static' => 0,
        'group_description' => '<p>If you choose to finance your BMW Motorcycle with another institution or bank, you can still use this platform to find your ride. Just estimate the total amount you\'re planning to pay and we\'ll help find a motorcycle that matches your requirements.At the end of the process, you\'ll be able to checkout with a no-obligation offer to purchase which you can then pass on to either your bank or your preferred BMW Retailer to arrange finance</p>',
        'is_business' => 0,
        'group_full_title' => 'BMW Motorrad Other Finance',
        'deposit_slider_steps' => null,
        'deposit_default_step' => null,
        'term_slider_steps' => null,
        'term_default_step' => null,
        'mileage_slider_steps' => null,
        'mileage_default_step' => null,
        'monthlypay_slider_steps' => null,
        'monthlypay_default_step' => null,
        'monthlypay_accuracy_to' => null,
        'monthlypay_accuracy_after' => null,
        'payinfull_step' => '10000',
        'payinfull_min' => '150000',
        'payinfull_max' => '5000000',
        'deposit_multiple_slider_steps' => null,
        'deposit_multiple_default_step' => null,
        'group_confirmation_popup' => null,
        'group_confirmation_popup_full' => null,
        'group_checkout_terms_and_conditions' => null,
        'grouped_under' => 'BMW Motorrad Other Finance',
        'balloon_slider_steps' => null,
        'balloon_default_step' => null
    ]
];

$financingOptionsGroupModel = Mage::getModel('rockar_financingoptions/group');

foreach (array_merge($bmwFinanceGroups, $miniFinanceGroup, $motorradFinanceGroup) as $group) {
    $financingOptionsGroupModel->load($group['group_title'], 'group_title')
        ->addData($group)
        ->save();
}

$this->endSetup();

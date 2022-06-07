<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Setup
 * @author    Ivans Zuks <info@scandiweb.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

$this->startSetup();
$connection = $this->getConnection();

// Data for each brand
$brandsData = [
    'MINI' => [
        'group_title' => 'MINI Cash Purchase',
        'group_description' => 'Ready. Set. Go. You\'ve got the cash - just decide the total amount you want to spend and we\'ll help find the perfect MINI for you. At checkout you can accept an offer to purchase, arrange payment and delivery online with your chosen MINI Retailer',
        'sort_order' => 130,
        'grouped_under' => 'MINI Cash Purchase',
        'store_id' => 3,
        'type' => 'cashmini'
    ],
    'MOTO' => [
        'group_title' => 'BMW Motorrad Cash Purchase',
        'group_description' => 'Ready. Set. Go. The cash option gives you the ability to self-fund your BMW Motorcycle purchase upfront and without interest or the need for a credit check. Just set your desired total spend and we\'ll assist you in finding the motorcycle that\'s right for you. At checkout you have the option to accept an offer to purchase and arrange payment and delivery online with your chosen BMW Motorrad Retailer.',
        'sort_order' => 230,
        'grouped_under' => 'BMW Motorrad Cash Purchase',
        'store_id' => 4,
        'type' => 'cashmoto'
    ],
    'BMW' => [
        'group_title' => 'BMW Cash Purchase',
        'group_description' => 'Ready. Set. Go. The cash option gives you the ability to self-fund your purchase upfront and without interest or the need for a credit check. Just set your desired total spend and we\'ll assist you in finding a BMW that suits your requirements. At checkout you have the option to accept an offer to purchase and arrange payment and delivery online with your chosen BMW Retailer.',
        'sort_order' => 30,
        'grouped_under' => 'BMW Cash Purchase',
        'store_id' => 2,
        'type' => 'cashbmw'
    ]
];

// Finance group data for every brand
$financeGroupData = [
    'group_title' => '',
    'group_full_title' => '',
    'sort_order' => 90,
    'is_static' => 0,
    'group_description' => '',
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
];

// Product finance data for every brand
$productFinanceData = [
    'customer_groups' => '0,1,3,4,5',
    'is_active' => 1,
    'header' => '<h3>Cash</h3><p>You\'ve got some cash burning a hole in your pocket or you are arranging some finance yourself</p>',
    'footer' => '<h5>Paying with cash is the simplest way to pay for your car</h5><p>All you have to do is pay the final balance which is a simple calculation which is the total value of the car minus any cash deposit you paid before the delivery and includes any equity in the car you are intending to swap as a part exchange.</p><ul><li>It is simple &ndash; paying by cash is a well-known upfront payment</li><li>Ownership from day 1 and can sell the car anytime you want to</li><li>Our finance partners will not need to do a credit check on you so it does not matter if you have a good credit rating</li></ul><h5>Key considerations:</h5><ul><li>If you plan to use your savings to pay for your new car, you are potentially limiting your immediate access to funds that would help you deal with uncertain events that life throws up.</li><li>If you decide to raise cash through a personal loan, then you may find your monthly payments are higher than manufacturer supported finance options.</li></ul>',
    'video' => '<p><iframe frameborder="0" height="360" src="https://forms.bmw.co.za/SelectVideo/DSP_BMW_Cash_.mp4" style="display: block; margin-left: auto; margin-right: auto;" width="640"></iframe></p>',
    'pay_in_full' => 1,
    'finance_company' => NULL,
    'minimum_amount_of_finance' => 0,
    'monthly_price_calc' => NULL,
    'total_amount_payable_calc' => NULL,
    'interest_charges_calc' => NULL,
    'method_type' => 1,
    'finance_type' => 1,
    'option_finance_type' => 1,
    'is_credit' => 0,
    'interest_rate_calc' => NULL
];

$financeGroupModel = Mage::getModel('rockar_financingoptions/group');
$productFinanceModel = Mage::getModel('rockar_financingoptions/options');

foreach($brandsData as $brandData) {
    // Create finance group (if it doesn't exist now)
    if (!$financeGroupModel->load($brandData['group_title'], 'group_title')->getId()) {
        $tempFinanceGroupData = $financeGroupData;
        $tempFinanceGroupData['group_title'] = $brandData['group_title'];
        $tempFinanceGroupData['group_full_title'] = $brandData['group_title'];
        $tempFinanceGroupData['group_description'] = $brandData['group_description'];
        $tempFinanceGroupData['sort_order'] = $brandData['sort_order'];
        $tempFinanceGroupData['grouped_under'] = $brandData['grouped_under'];
        $financeGroupModel->setData($tempFinanceGroupData)
            ->save();
        unset($tempFinanceGroupData);
    }

    // Create Product Finance for created group
    if (!$productFinanceModel->load($brandData['type'], 'type')->getId()) {
        $tempProductFinanceData = $productFinanceData;
        $tempProductFinanceData['title'] = $brandData['group_title'];
        $tempProductFinanceData['type'] = $brandData['type'];
        $tempProductFinanceData['stores'] = $brandData['store_id'];
        $tempProductFinanceData['group_id'] = $financeGroupModel->getId();
        $productFinanceModel->setData($tempProductFinanceData)
            ->save();
        unset($tempProductFinanceData);

        // Copy variables finance overlay
        $productFinanceId = $productFinanceModel->getId();
        $optionIds = Mage::getModel('rockar_financingoptions/options')
            ->load('Cash', 'title')
            ->getOptionsId();

        $optionVariables = Mage::getModel('peppermint_financingoptions/options_variables')->getCollection()
            ->addFieldToFilter('option_id', $optionIds)
            ->getData();

        foreach ($optionVariables as $key => $value) {
            unset($optionVariables[$key]['entity_id']);
            $optionVariables[$key]['option_id'] = $productFinanceId;
        }
        $productFinanceModel->setData('variables_link_data', $optionVariables);

        // Copy variables finance quote
        $optionPdpVariables = Mage::getModel('peppermint_financingoptions/options_pdp_variables')->getCollection()
            ->addFieldToFilter('option_id', $optionIds)
            ->getData();

        foreach ($optionPdpVariables as $key => $value) {
            unset($optionPdpVariables[$key]['entity_id']);
            $optionPdpVariables[$key]['option_id'] = $productFinanceId;
        }
        $productFinanceModel->setData('pdp_variables_link_data', $optionPdpVariables)
            ->save();
    }

    $financeGroupModel->unsetData();
    $productFinanceModel->unsetData();
}

$this->endSetup();

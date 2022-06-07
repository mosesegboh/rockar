<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Setup
 * @author    Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright Copyright (c) 2019 Rockar Ltd (http://rockar.com)
 */

$this->startSetup();

$prefixes = [
    'bmw',
    'mini',
    'motorrad'
];
$storeViewModel = Mage::getModel('core/store');
$storeId = [];
foreach ($prefixes as $prefix) {
    $storeId[$prefix] = $storeViewModel->load($prefix . '_store_view', 'code')->getId();
}

$bmwFinanceGroups = [
    [
        'group_title' => 'BMW Select Finance',
        'website' => $storeId['bmw'],
        'method_type' => Rockar_FinancingOptions_Model_Adminhtml_System_Config_Source_MethodType::TYPE_LEASING
    ],
    [
        'group_title' => 'BMW Instalment Sale',
        'website' => $storeId['bmw'],
        'method_type' => Peppermint_FinancingOptions_Model_Adminhtml_System_Config_Source_MethodType::TYPE_INSTALMENT
    ],
    [
        'group_title' => 'Cash Purchase',
        'website' => $storeId['bmw'],
        'method_type' => Rockar_FinancingOptions_Model_Adminhtml_System_Config_Source_MethodType::TYPE_PAY_IN_FULL
    ],
    [
        'group_title' => 'BMW Other Finance',
        'website' => $storeId['bmw'],
        'method_type' => Rockar_FinancingOptions_Model_Adminhtml_System_Config_Source_MethodType::TYPE_PAY_IN_FULL
    ]
];

$miniFinanceGroup = [
    [
        'group_title' => 'MINI Select Finance',
        'website' => $storeId['mini'],
        'method_type' => Rockar_FinancingOptions_Model_Adminhtml_System_Config_Source_MethodType::TYPE_LEASING
    ],
    [
        'group_title' => 'MINI Instalment Sale',
        'website' => $storeId['mini'],
        'method_type' => Peppermint_FinancingOptions_Model_Adminhtml_System_Config_Source_MethodType::TYPE_INSTALMENT
    ],
    [
        'group_title' => 'MINI Cash Purchase',
        'website' => $storeId['mini'],
        'method_type' => Rockar_FinancingOptions_Model_Adminhtml_System_Config_Source_MethodType::TYPE_PAY_IN_FULL
    ],
    [
        'group_title' => 'MINI Other Finance',
        'website' => $storeId['mini'],
        'method_type' => Rockar_FinancingOptions_Model_Adminhtml_System_Config_Source_MethodType::TYPE_PAY_IN_FULL
    ]
];

$motorradFinanceGroup = [
    [
        'group_title' => 'BMW Motorrad Select',
        'website' => $storeId['motorrad'],
        'method_type' => Rockar_FinancingOptions_Model_Adminhtml_System_Config_Source_MethodType::TYPE_LEASING
    ],
    [
        'group_title' => 'BMW Motorrad Instalment Sale',
        'website' => $storeId['motorrad'],
        'method_type' => Peppermint_FinancingOptions_Model_Adminhtml_System_Config_Source_MethodType::TYPE_INSTALMENT
    ],
    [
        'group_title' => 'BMW Motorrad Cash Purchase',
        'website' => $storeId['motorrad'],
        'method_type' => Rockar_FinancingOptions_Model_Adminhtml_System_Config_Source_MethodType::TYPE_PAY_IN_FULL
    ],
    [
        'group_title' => 'BMW Motorrad Other Finance',
        'website' => $storeId['motorrad'],
        'method_type' => Rockar_FinancingOptions_Model_Adminhtml_System_Config_Source_MethodType::TYPE_PAY_IN_FULL
    ]
];

foreach (array_merge($bmwFinanceGroups, $miniFinanceGroup, $motorradFinanceGroup) as $group) {
    $variableModel = Mage::getModel('rockar_financingoptions/group')->load($group['group_title'], 'group_title');
    $variableModel->addData($group)
        ->save();
}

$this->endSetup();

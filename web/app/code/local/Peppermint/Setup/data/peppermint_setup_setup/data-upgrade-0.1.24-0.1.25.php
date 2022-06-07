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
        'type' => 'cashmini'
    ],
    'MOTO' => [
        'type' => 'cashmoto'
    ],
    'BMW' => [
        'type' => 'cashbmw'
    ]
];

$productFinanceModel = Mage::getModel('rockar_financingoptions/options');
$optionIds = $productFinanceModel->load('cashb', 'type')
    ->getOptionsId();

$productFinanceModel->unsetData();

$optionPdpVariables = Mage::getModel('peppermint_financingoptions/options_pdp_variables')->getCollection()
    ->addFieldToFilter('option_id', $optionIds);

$optionVariables = Mage::getModel('peppermint_financingoptions/options_variables')->getCollection()
    ->addFieldToFilter('option_id', $optionIds);

foreach($brandsData as $brandData) {
    if ($productFinanceId = $productFinanceModel->load($brandData['type'], 'type')->getId()) {
        // Copy variables finance overlay
        $newVariablesFO = [];
        foreach ($optionVariables as $key => $value) {
            $newVariablesFO[$value->getVariableId()] = [
                'sort_order' => $value->getSortOrder(),
                'variable_title' => $value->getVariableTitle()
            ];
        }
        $productFinanceModel->setData('variables_link_data', $newVariablesFO);

        // Copy variables finance quote
        $newVariablesFQ = [];
        foreach ($optionPdpVariables as $key => $value) {
            $newVariablesFQ[$value->getVariableId()] = [
                'sort_order' => $value->getSortOrder(),
                'variable_title' => $value->getVariableTitle()
            ];
        }
        $productFinanceModel->setData('pdp_variables_link_data', $newVariablesFQ)
            ->save();
    }

    unset($newVariablesFO);
    unset($newVariablesFQ);
    $productFinanceModel->unsetData();
}

$this->endSetup();

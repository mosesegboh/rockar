<?php

/**
 * @category     Setup
 * @package      Peppermint_Setup
 * @author       Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

// Change from "Installment" to "Instalment"
// plus test formatting and update grouped_under

$this->startSetup();

$titles = [
    'BMW Select Finance' => [
        'group_title' => 'BMW Select Finance',
        'group_full_title' => 'BMW Select Finance',
        'grouped_under' => 'BMW Select Finance'
    ],
    'BMW Installment Sale' => [
        'group_title' => 'BMW Instalment Sale',
        'group_full_title' => 'BMW Instalment Sale',
        'grouped_under' => 'BMW Instalment Sale'
    ],
    'CASH' => [
        'group_title' => 'Cash',
        'group_full_title' => 'Cash Purchase',
        'grouped_under' => 'Cash Purchase'
    ]
];

foreach ($titles as $titleOld => $titleNew) {
    $var = Mage::getModel('rockar_financingoptions/group')->load($titleOld, 'group_title');
    if ($var->getId()) {
        $var->addData([
            'group_title' => $titleNew['group_title'],
            'group_full_title' => $titleNew['group_full_title'],
            'grouped_under' => $titleNew['grouped_under']
        ])->save();
    }
}

$this->endSetup();

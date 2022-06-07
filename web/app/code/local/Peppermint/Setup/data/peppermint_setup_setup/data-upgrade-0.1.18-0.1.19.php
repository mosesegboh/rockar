<?php
/**
 * @category     Setup
 * @package      Peppermint_Setup
 * @author       Cosmin Chidovat <chidovat.cosmin@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

// Update terms slider values and default values

$this->startSetup();

$titles = [
    'BMW Select Finance' => [
        'term_slider_steps' => '12,18,24,30,36,42,48,53,55',
        'term_default_step' => '48'
    ],
    'BMW Instalment Sale' => [
        'term_slider_steps' => '12,18,24,30,36,42,48,53,55,60,72',
        'term_default_step' => '60'
    ]
];

foreach ($titles as $titleOld => $titleNew) {
    $var = Mage::getModel('rockar_financingoptions/group')->load($titleOld, 'group_title');
    if ($var->getId()) {
        $var->addData([
            'term_slider_steps' => $titleNew['term_slider_steps'],
            'term_default_step' => $titleNew['term_default_step']
        ])->save();
    }
}

$this->endSetup();

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Dinu Brie <dinu.brie@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

$this->startSetup();
$financingGroupModel = Mage::getModel('rockar_financingoptions/group');

$duplicateCashOption = $financingGroupModel->load('Cash Purchase', 'group_title');

if ($duplicateCashOption->getId()) {
    $duplicateCashOption->delete();
}
$groupTitle = [
    'MINI Cash Purchase' => 'MINI Cash',
    'BMW Motorrad Cash Purchase' => 'BMW Motorrad Cash',
    'BMW Cash Purchase' => 'BMW Cash'
];

foreach ($groupTitle as $title => $newTitle) {
    if ($financingGroupModel->load($title, 'group_title')->getId()) {
        $financingGroupModel->setGroupTitle($newTitle)
            ->setGroupFullTitle('Cash')
            ->save();
    }
}

$this->endSetup();

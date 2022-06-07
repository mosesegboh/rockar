<?php
/**
 * @category  Peppermint
 * @package   Peppermint_NewJourney
 * @author    Kalvis Ostrovskis <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();

$checkboxesCollection = Mage::getModel('rockar_partexchange/conditions_checkboxes')->getCollection()
    ->applyDefaultFilters();

foreach ($checkboxesCollection as $checkbox) {
    if ($checkbox->getTitle() === 'My trade-in vehicle still under warranty.') {
        $checkbox->setTitle('My trade-in vehicle is still under warranty.')
            ->save();
        break;
    }
}

$installer->endSetup();

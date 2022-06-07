<?php
/**
 * @category     Peppermint
 * @package      Peppermint_Orderstatus
 * @author       Dinu Brie <dinu.brie@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

$this->startSetup();
try {
    $demoStore = Mage::getModel('rockar_orderstatus/status')->getCollection()
        ->addFieldToFilter('store_id', 0);
    foreach ($demoStore as $storeId) {
        $storeId->setIsActive(0)
            ->save();
    }
} catch (Exception $e) {
    Mage::logException($e);
}
$this->endSetup();

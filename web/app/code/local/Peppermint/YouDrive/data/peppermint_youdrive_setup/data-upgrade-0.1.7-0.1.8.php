<?php
/**
 * @category  Peppermint
 * @package   Peppermint_YouDrive
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (https://rockar.com)
 */

/** @var Mage_Customer_Model_Entity_Setup $installer */
$installer = $this;
$installer->startSetup();
$table = $installer->getTable('rockar_youdrive/booking');
try {
    $installer->run(
        'UPDATE ' . $table .
        ' SET `booking_placed` = 0' .
        ' WHERE `booking_placed` = 1 AND `local_store_code` is not null'
    );
} catch (Exception $e) {
    Mage::logException($e);
}

$this->endSetup();
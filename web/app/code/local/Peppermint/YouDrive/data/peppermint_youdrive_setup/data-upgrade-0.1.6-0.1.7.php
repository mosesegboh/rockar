<?php
/**
 * @category  Peppermint
 * @package   Peppermint_YouDrive
 * @author    Krists Dadzitis <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (https://rockar.com)
 */

/** @var Mage_Customer_Model_Entity_Setup $installer */
$installer = $this;
$installer->startSetup();

try {
    $data = [
        'bmw_store_view' => 'Test Drive',
        'mini_store_view' => 'Test Drive',
        'motorrad_store_view' => 'Test Ride'
    ];

    $storeSingleton = Mage::getSingleton('core/store');

    foreach ($data as $storeCode => $value) {
        $installer->setConfigData(
            Peppermint_YouDrive_Helper_Data::XML_PATH_TEST_DRIVE_TITLE,
            $value,
            'stores',
            $storeSingleton->load($storeCode, 'code')->getId()
        );
    }
} catch (Exception $e) {
    Mage::logException($e);
}

$this->endSetup();
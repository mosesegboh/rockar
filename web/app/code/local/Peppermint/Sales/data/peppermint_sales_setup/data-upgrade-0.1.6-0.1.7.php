<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Sykander Gul <sykander.gul@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();

$documentPathHelper = Mage::helper('peppermint_checkout/pdf');
$documentHelper = Mage::helper('peppermint_sales/document_otp');
$ordersCollection = Mage::getModel('sales/order')->getCollection()
    ->addFieldToSelect('increment_id')
    ->addFieldToSelect('entity_id');

foreach ($ordersCollection as $order) {
    $incrementId = $order->getIncrementId();

    $path = $documentPathHelper->getOtpFilePath($incrementId);

    if (file_exists($path)) {
        $documentHelper->createRecord(
            $order->getId(),
            $incrementId
        );
    }
}

$installer->endSetup();

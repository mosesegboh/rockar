<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Sykander Gul <sykander.gul@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();

$documentCollection = Mage::getModel('peppermint_sales/order_document')->getCollection()
    ->addFieldToFilter('name', ['like' => '%.pdf']);

// Remove the .pdf extension in document names for records which already exist
foreach ($documentCollection as $document) {
    $document->setName(
        substr($name = $document->getName(), 0, strripos($name, '.pdf'))
    )
        ->save();
}

$installer->endSetup();

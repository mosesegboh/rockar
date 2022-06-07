<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Sykander Gul <sykander.gul@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();

// Add .pdf extension to file names again
foreach (Mage::getModel('peppermint_sales/order_document')->getCollection() as $document) {
    $name = $document->getName();

    if (strpos($name, '.pdf') === false) {
        $document->setName(
            $name . '.pdf'
        )
            ->save();
    }
}

$installer->endSetup();

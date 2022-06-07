<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Setup
 * @author    Cristian Moga <cristian.moga@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

$this->startSetup();

// BMW brand data
$bmwBrandData = [
    'header' => '<h3>Cash</h3><p>You\'d like to finance your BMW with cash up front.</p>',
    'footer' => '<h3>Cash</h3><p>Ready. Set. Go. The cash option gives you the ability to self-fund your purchase upfront and without interest or the need for a credit check. Just set your desired total spend and we\'ll assist you in finding a BMW that suits your requirements. At checkout you have the option to accept an offer to purchase and arrange payment and delivery online with your chosen BMW Retailer.</p>',
    'type' => 'cashbmw'
];

$productFinanceModel = Mage::getModel('rockar_financingoptions/options');

// Data update
if ($productFinanceModel->load($bmwBrandData['type'], 'type')->getId()) {
    $productFinanceModel->setHeader($bmwBrandData['header'])
        ->setFooter($bmwBrandData['footer'])
        ->save();
}

$this->endSetup();

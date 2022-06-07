<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Setup
 * @author    Ivans Zuks <info@scandiweb.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

$this->startSetup();

// Data for each brand
$brandsData = [
    'MINI' => [
        'header' => '<h3>Cash</h3><p>I\'ve got the cash to pay for my MINI up front</p>',
        'footer' => '<h3>Cash</h3><p>Ready. Set. Go. You\'ve got the cash - just decide the total amount you want to spend and we\'ll help find the perfect MINI for you.At checkout you can accept an offer to purchase, arrange payment and delivery online with your chosen MINI Retailer.</p>',
        'position' => 90,
        'type' => 'cashmini',
        'video' => '<p><iframe frameborder="0" height="360" src="https://forms.bmw.co.za/SelectVideo/MINI_Cash.mp4" style="display: block; margin-left: auto; margin-right: auto;" width="640"></iframe></p>'
    ],
    'MOTO' => [
        'header' => '<h3>Cash</h3><p>You\'d like to buy your motorcycle up front with cash</p>',
        'footer' => '<h3>Cash</h3><p>Ready. Set. Go. The cash option gives you the ability to self-fund your BMW Motorcycle purchase upfront and without interest or the need for a credit check. Just set your desired total spend and we\'ll assist you in finding the motorcycle that\'s right for you. At checkout you have the option to accept an offer to purchase and arrange payment and delivery online with your chosen BMW Motorrad Retailer</p>',
        'position' => 90,
        'type' => 'cashmoto',
        'video' => '<p><iframe frameborder="0" height="360" src="https://forms.bmw.co.za/SelectVideo/Motorrad_Cash.mp4" style="display: block; margin-left: auto; margin-right: auto;" width="640"></iframe></p>'
    ],
    'BMW' => [
        'position' => 90,
        'type' => 'cashbmw'
    ]
];

$productFinanceModel = Mage::getModel('rockar_financingoptions/options');

foreach($brandsData as $brandData) {
    // Data upload
    if ($productFinanceModel->load($brandData['type'], 'type')->getId()) {
        if ($brandData['type'] !== 'cashbmw') {
            $productFinanceModel->setHeader($brandData['header'])
                ->setFooter($brandData['footer'])
                ->setVideo($brandData['video']);
        }

        $productFinanceModel->setPosition($brandData['position'])
            ->save();
    }

    $productFinanceModel->unsetData();
}

$this->endSetup();

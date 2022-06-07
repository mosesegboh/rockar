<?php
/**
 * @category  Peppermint
 * @package   Peppermint_YouDrive
 * @author    Taras Kapushchak <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

/** @var Mage_Sales_Model_Entity_Setup $installer */
$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();

$personalDetailsBlock = [
    'bmw_store_view' => [
        'title' => 'BMW Test Drive Personal Details Statement',
        'identifier' => 'youdrive_personal_details_statement',
        'content' => 'BMW Group South Africa will share your information below ' .
            'with your chosen BMW Retailer to enable them to contact you to fulfil ' .
            'your request. For further information on how we use your data, ' .
            'international data transfers and your rights, please see ' .
            '<a href="https://www.bmw.co.za/en/footer/metanavigation/legal-disclaimer-pool/privacy-statement.html">Privacy Policy</a>',
        'stores' => 0,
        'is_active' => 1
    ],
    'mini_store_view' => [
        'title' => 'MINI Test Drive Personal Details Statement',
        'identifier' => 'youdrive_personal_details_statement',
        'content' => 'BMW Group South Africa will share your information below ' .
            'with your chosen MINI Retailer to enable them to contact you to fulfil ' .
            'your request. For further information on how we use your data, ' .
            'international data transfers and your rights, please see ' .
            '<a href="https://www.bmw.co.za/en/footer/metanavigation/legal-disclaimer-pool/privacy-statement.html">Privacy Policy</a>',
        'stores' => 0,
        'is_active' => 1
    ],
    'motorrad_store_view' => [
        'title' => 'Motorrad Test Drive Personal Details Statement',
        'identifier' => 'youdrive_personal_details_statement',
        'content' => 'BMW Group South Africa will share your information below ' .
            'with your chosen Motorrad Retailer to enable them to contact you to fulfil ' .
            'your request. For further information on how we use your data, ' .
            'international data transfers and your rights, please see ' .
            '<a href="https://www.bmw.co.za/en/footer/metanavigation/legal-disclaimer-pool/privacy-statement.html">Privacy Policy</a>',
        'stores' => 0,
        'is_active' => 1
    ]
];

foreach ($personalDetailsBlock as $storeCode => $blockData) {
    $storeId = Mage::getModel('core/store')->load($storeCode, 'code')->getId();
    $blockData['stores'] = $storeId;
    Mage::getModel('cms/block')->setStoreId($storeId)
        ->load($blockData['identifier'])
        ->addData($blockData)
        ->save();
}

$installer->endSetup();

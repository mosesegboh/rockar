<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Setup
 * @author    Mariam Khelashvili <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (https://rockar.com)
 */

/** @var  $installer */
$installer = $this;
$blocks = [
    [
        'identifier' => 'checkout-your-address-gdpr-statement',
        'title' => 'Checkout Your Address GDPR Statement',
        'content' => <<<EOF
<p><span>Please complete your details below to proceed with your order. </span></p><p><span>For further information on how we use your data and your right to the protection of your personal information, please see our&nbsp;</span><a href="https://www.bmw.co.za/en/footer/metanavigation/legal-disclaimer-pool/privacy-statement.html" target="_blank">Privacy&nbsp;Statement<span>.</span></a></p>
EOF
    ]
];

$bmwStoreId = Mage::getModel('core/store')->load('bmw_store_view', 'code')->getId();

$installer->startSetup();

foreach ($blocks as $info) {
    $cmsBlock = Mage::getModel('cms/block')->getCollection()
        ->addFieldToFilter('identifier', $info['identifier'])
        ->addStoreFilter($bmwStoreId, false)
        ->getFirstItem();

    if ($cmsBlock->getId()) {
        $cmsBlock->setContent($info['content'])->setStores([$bmwStoreId])->save();
    } else {
        Mage::getModel('cms/block')
            ->setIdentifier($info['identifier'])
            ->setContent($info['content'])
            ->setTitle($info['title'])
            ->setIsActive(1)
            ->setStores([$bmwStoreId])
            ->save();
    }
}

$installer->endSetup();

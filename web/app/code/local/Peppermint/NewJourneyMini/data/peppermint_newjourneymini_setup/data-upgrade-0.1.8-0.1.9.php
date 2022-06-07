<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Setup
 * @author    Taras Kapushchak <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (https://rockar.com)
 */

/** @var  $installer */
$installer = $this;
$blocks = [
    [
        'identifier' => 'checkout-your-address-gdpr-statement',
        'title' => 'Checkout Your Address GDPR Statement',
        'content' => <<<EOF
<p>The journey of thousands of MINI drivers starts with just a couple of clicks.<br>
Let’s get started by filling out your personal information below.</p>
<p>And remember, you are welcome to learn more about how MINI uses and protects
your data at any time. Simply see our
<a href="https://www.mini.co.za/en_ZA/home/privacystatement.html" target="_blank">Privacy&nbsp;Statement</a>
for full breakdown of our policy.</p>
EOF
    ]
];

$miniStoreId = Mage::getModel('core/store')->load('mini_store_view', 'code')->getId();

$installer->startSetup();

foreach ($blocks as $info) {
    $cmsBlock = Mage::getModel('cms/block')->getCollection()
        ->addFieldToFilter('identifier', $info['identifier'])
        ->addStoreFilter($miniStoreId, false)
        ->getFirstItem();

    if ($cmsBlock->getId()) {
        $cmsBlock->setContent($info['content'])->setStores([$miniStoreId])->save();
    } else {
        Mage::getModel('cms/block')
            ->setIdentifier($info['identifier'])
            ->setContent($info['content'])
            ->setTitle($info['title'])
            ->setIsActive(1)
            ->setStores([$miniStoreId])
            ->save();
    }
}

$installer->endSetup();

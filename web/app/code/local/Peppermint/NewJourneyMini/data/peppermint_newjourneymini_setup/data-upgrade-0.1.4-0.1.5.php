<?php
/**
 * @category  Peppermint
 * @package   Peppermint_NewJourney
 * @author    Ketevani Revazishvili <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (https://rockar.com)
 */

/** @var  $installer */
$installer = $this;
$blocks = [
    [
        'identifier' => 'footer_social_media',
        'title' => 'Footer Social Media',
        'content' => <<<EOF
<p class="social-media-title">Find us on</p>
<ul class="social-block">
<li>
<a class="facebook" href="https://www.facebook.com/MINI.southafrica" target="_blank"></a>
<a class="twitter" href="https://twitter.com/MINISouthAfrica" target="_blank"></a>
<a class="youtube" href="https://www.youtube.com/channel/UCl36VfpvrfZZuaK5_0Oc3IA" target="_blank"></a>
<a class="instagram" href="https://www.instagram.com/mini_southafrica/" target="_blank"></a>
</li>
</ul>
EOF
    ],
    [
        'identifier' => 'footer_bottom_links',
        'title' => 'Footer Bottom Links',
        'content' => <<<EOF
<div class="footer-bottom-links">
<ul>
<li><a class="dsp2-link-s" href="https://www.mini.co.za/en_ZA/home/LockdownGuide.html" target="_blank">FAQs</a></li>
<li><a class="dsp2-link-s" href="https://www.mini.co.za/en_ZA/home/explore/pricelist.html" target="_blank">Price List</a></li>
<li><a class="dsp2-link-s" href="https://www.mini.co.za/en_ZA/home/footer/contact-us.html" target="_blank">Contact Us</a></li>
<li><a class="dsp2-link-s" href="https://www.mini.co.za/en_ZA/home/footer/Legal.html" target="_blank">Legal</a></li>
<li><a class="dsp2-link-s" href="https://www.mini.co.za/en_ZA/home/footer/cookies.html" target="_blank">Cookies</a></li>
<li><a class="dsp2-link-s" href="https://www.mini.co.za/en_ZA/home/footer/bmw-group-websites.html" target="_blank">BMW Group Websites</a></li>
<li><a class="dsp2-link-s" href="https://www.mini.co.za/en_ZA/home/footer/careers.html" target="_blank">Careers</a></li>
<li><a class="dsp2-link-s" href="https://www.mini.co.za/en_ZA/home/privacystatement.html" target="_blank">Privacy Statement</a></li>
<li><a class="dsp2-link-s" href="https://www.mini.co.za/en_ZA/home/services/mini-road-assistance.html" target="_blank">MINI Assist</a></li>
<li><a class="dsp2-link-s" href="https://www.mini.co.za/en_ZA/home/footer/paia-and-popia-manual.html" target="_blank">PAIA</a></li>
</div>
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

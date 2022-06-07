<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Setup
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (https://rockar.com)
 */

/** @var  $installer */
$installer = $this;
$blocks = [
    [
        'identifier' => 'footer_essential_information_left',
        'title' => 'Footer Essential Information, Left',
        'content' => <<<EOF
<ul class="footer-menu">
<li>
<h4>Buyer's Choice</h4>
<ul class="footer-submenu">
<li><a href="https://www.bmw.co.za/en/all-models.html">Build Now</a></li>
<li><a href="http://www.bmwgroup-media.co.za/digital/documents/pricelist/BMW_Range_Price_List.pdf" target="_blank">Range Price List</a></li>
<li><a href="http://www.bmwgroup-media.co.za/digital/documents/brochure/BMW-FullRange.pdf" target="_blank">Range Brochure</a></li>
</ul>
</li>
</ul>
EOF
    ],
    [
        'identifier' => 'footer_essential_information_right',
        'title' => 'Footer Essential Information, Right',
        'content' => <<<EOF
<ul class="footer-menu">
<li>
<h4>Explore BMW</h4>
<ul class="footer-submenu">
<li><a href="https://www.bmwgroup.jobs/za" target="_blank">Careers</a></li>
<li><a href="https://www.bmw-motorrad.co.za/en/home.html" target="_blank">BMW Motorrad</a></li>
<li><a href="https://www.bmw.com/en/index.html" target="_blank">BMW.com</a></li>
</ul>
</li>
</ul>
EOF
    ],
    [
        'identifier' => 'footer_quick_links',
        'title' => 'Footer Quick Links (BMW)',
        'content' => <<<EOF
<ul class="footer-menu">
<li>
<h4>Visit Us</h4>
<ul class="footer-submenu">
<li><a href="https://www.youtube.com/user/BMWSouthAfrica" target="_blank">YouTube</a></li>
<li><a href="https://www.twitter.com/BMW_SA" target="_blank">Twitter</a></li>
<li><a href="https://www.facebook.com/BMWSA" target="_blank">Facebook</a></li>
<li><a href="https://www.linkd.in/17I4hQ2" target="_blank">LinkedIn</a></li>
<li><a href="https://www.instagram.com/bmwsouthafrica/" target="_blank">Instagram</a></li>
</ul>
</li>
</ul>
EOF
    ],
    [
        'identifier' => 'footer_follow_us',
        'title' => 'Footer Follow Us',
        'content' => <<<EOF
<ul class="footer-menu">
<li>
<h4>Financial Services</h4>
<ul class="footer-submenu">
<li><a href="https://www.bmw.co.za/en/topics/offers-and-services/bmw-financial-services/investor-relations.html">Investor Relations</a></li>
<li><a href="https://www.bmw.co.za/en/topics/offers-and-services/bmw-financial-services/terms.html">Terms &amp; Conditions</a></li>
</ul>
</li>
</ul>
EOF
    ],
    [
        'identifier' => 'footer_social_media',
        'title' => 'Footer Social Media',
        'content' => <<<EOF
<ul class="social-block">
<li>
    <p class="h7">Social Media</p>
</li>
<li>
<a class="youtube" href="https://www.youtube.com/user/BMWSouthAfrica" target="_blank"></a>
<a class="twitter" href="http://www.twitter.com/BMW_SA" target="_blank"></a>
<a class="facebook" href="http://www.facebook.com/BMWSA" target="_blank"></a>
<a class="linkedin" href="http://linkd.in/17I4hQ2" target="_blank"></a>
<a class="instagram" href="https://www.instagram.com/bmwsouthafrica/" target="_blank"></a>
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
<li><a href="https://www.bmw.co.za/en/integration/contact-us.html" target="_blank">Contact Us</a></li>
<li><a href="https://www.bmw.co.za/en/footer/footer-section/cookie-policy.html" target="_blank">Cookies</a></li>
<li><a href="https://www.bmw.co.za/en/footer/metanavigation/legal-disclaimer-pool/legal-disclaimer.html" target="_blank">Legal Notice</a></li>
<li><a href="https://www.bmw.co.za/en/footer/metanavigation/legal-disclaimer-pool/paia-manual.html" target="_blank">PAIA Manual</a></li>
<li><a href="https://www.bmw.co.za/en/footer/metanavigation/legal-disclaimer-pool/privacy-statement.html" target="_blank">Privacy Statement</a></li>
<li><a href="https://forms.bmw.co.za/documents/BMW-BBBEE-Certificate.pdf" target="_blank">B-BBBEEE Certificate</a></li>
<li><a href="https://www.bmw.co.za/en/footer/metanavigation/legal-disclaimer-pool/notice.html" target="_blank">Notice Board</a></li>
<li><a href="https://www.bmw.co.za/en/topics/offers-and-services/bmw-financial-services/legislation.html" target="_blank">Compliance</a></li>
</ul>
</div>
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

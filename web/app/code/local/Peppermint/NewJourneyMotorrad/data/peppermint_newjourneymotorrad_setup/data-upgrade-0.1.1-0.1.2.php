<?php
/**
 * @category  Peppermint
 * @package   Peppermint_NewJourneyMotorrad
 * @author    Moses Egboh <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (https://rockar.com)
 */

/** @var  $installer */
$installer = $this;
$blocks = [
    [
        'identifier' => 'footer_bottom_one',
        'title' => 'Footer Essential Information, Left',
        'content' => <<<EOF
        <div class="back-to-the-top">BACK TO THE TOP</div>
        <div class="first">
            <div class="first-inner">
                <span class="find-bmw">Find your BMW Mottorad Bike</span>
                <div class="all-models">
                    <div class="inside-all-models"><span class="grid-icon"><span class="span-grid"><a href="https://www.bmw-motorrad.co.za/en/models/modeloverview.html">ALL MODELS</a></span></span></div>
                </div>
                <div class="social-media">
                    <span class="facebook-icon"><span class="span-facebook"><a href="https://www.facebook.com/BMWMotorradSA">Facebook</a></span></span>
                    <span class="twitter-icon"><span class="span-twitter"><a href="https://twitter.com/BMWMotorradSA">Twitter</a></span></span>
                    <span class="youtube-icon"><span class="span-youtube"><a href="https://www.youtube.com/user/BMWMotorradSA">Youtube</span></a></span>
                    <span class="instagram-icon"><span class="span-instagram"><a href="https://www.instagram.com/bmwmotorradsa/?hl=en">Instagram</span></a></span>
                </div>
            </div>
        </div>
        <div class="second">
            <div class="second-inner"> 
                <ul>
                    <li><span><a href="https://www.bmw-motorrad.co.za/en/experience/motorsport.html">BMW Mottorad Motorsport</a></span></li>
                    <li><span><a href="https://www.bmw.co.za/en/index.html">BMW</a></span></li>
                    <li><span><a href="https://www.mini.co.za/en_ZA/home.html">MINI</a></span></li>
                    <li><span><a href="https://www.bmwgroup.com/en.html">BMW Group</a></span></li>
                    <li><span><a href="https://www.bmw-motorrad.co.za/en/models/authority-vehicles.html">Authorities Vehicles</a></span></li>
                    <li><span><a href="http://www.bmwrideracademy.co.za/?">Riders Academy</a></span></li>
                    <li><span><a href="https://www.bmw-motorrad.co.za/en/public-pool/content-pool/paia-and-popia.html">PAIA & POPIA Manual</a></span></li>
                    <li><span><a href="https://www.bmw.co.za/en/topics/offers-and-services/bmw-financial-services/index.html">BMW Financial Services</a></span></li>
                </ul>
            </div>
        </div>
        <div class="third">
            <div class="second-inner"> 
                <ul>
                    <li><span><a href="https://www.bmw-motorrad.co.za/en/public-pool/content-pool/cookie-disclaimer.html">Cookies</a></span></li>
                    <li><span><a href="https://www.bmw-motorrad.co.za/en/public-pool/content-pool/privacy.html">Privacy Policy</a></span></li>
                    <li><span><a href="https://www.bmw-motorrad.co.za/en/public-pool/content-pool/imprint.html">Publishing Notes</a></span></li>
                    <li><span><a href="https://www.bmw-motorrad.co.za/en/public-pool/content-pool/legalnotice.html">Legal Disclaimer</a></span></li>
                    <li><span><a href="https://www.bmw-motorrad.co.za/en/public-pool/content-pool/contact.html">Contact</a></span></li>
                    <li><span class="reach-icon"><span class="reach"><a href="https://www.global.bmw-motorrad.com/en/reach/reach-en.html">REACH</a></span></span></li>
                </ul>
            </div>
        </div>
EOF
    ],
    [
        'identifier' => 'footer_bottom_two',
        'title' => 'Footer Bottom Two',
        'content' => <<<EOF
        <div class="lower-footer-left">
            <div>
                <span>&copy; BMW AG 2021</span><br>
                <span>Note: All motorcycles are supplied only with equipment required by law (e.g reflectors as per Euro 4 standard). 
                    The motorcycles depicted in the figure and videos on this website may also differ. Images may include optional extras.
                </span>
            </div>
        </div>
        <div class="lower-footer-right"> 
            <div class="lower-footer-right-inner">
                <span class="title-footer-right"><strong>MAKE A LIFE RIDE</strong></span> 
            </div>
        </div>
EOF
    ],
];

$motorradStoreId = Mage::getModel('core/store')->load('motorrad_store_view', 'code')->getId();

$installer->startSetup();

foreach ($blocks as $info) {
    $cmsBlock = Mage::getModel('cms/block')->getCollection()
        ->addFieldToFilter('identifier', $info['identifier'])
        ->addStoreFilter($motorradStoreId, false)
        ->getFirstItem();

    if ($cmsBlock->getId()) {
        $cmsBlock->setContent($info['content'])->setStores([$motorradStoreId])->save();
    } else {
        Mage::getModel('cms/block')
            ->setIdentifier($info['identifier'])
            ->setContent($info['content'])
            ->setTitle($info['title'])
            ->setIsActive(1)
            ->setStores([$motorradStoreId])
            ->save();
    }
}

$installer->endSetup();

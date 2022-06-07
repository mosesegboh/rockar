<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Setup
 * @author    Andrian Kogoshvili <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();
$bmwStoreId = Mage::getModel('core/store')->load('bmw_store_view', 'code')->getId();

if (!Mage::getModel('cms/block')->load('checkout_summary_legal_disclaimer')->getId()) {
    $block = Mage::getModel('cms/block');
    $block->setTitle('Checkout Summary Legal Disclaimer');
    $block->setIdentifier('checkout_summary_legal_disclaimer');
    $block->setStores([$bmwStoreId]);
    $block->setIsActive(1);
    $block->setContent(
        'Legal Disclaimer<br>
        Finance available through BMW Financial Services 
        (South Africa (Pty) Ltd., an Authorised Financial Services (FSP 4623) and 
        Registered Credit Provider NCRCP2341 (“BMW Financial Services”))<br>
        Any information obtained on this website calculator will not constitute a finance quote 
        as contemplated by the National Credit Act (No. 34 of 2005) (“NCA”). Any information 
        provided through the calculator and/or the website, regarding our finance products are 
        subject to change and/or may be amended at any time. This calculator is based on an indicative 
        interest rate. The actual interest rate offered by BMW Financial Services, should you apply for 
        vehicle finance, is dependant on your individual credit profile and may differ from the interest 
        rate indicated in the calculator. The calculator is also based on the condition of your current 
        vehicle to be traded in as reported by you and any outstanding finance you have declared. Your 
        monthly payment will alter if either of these change. No responsibility for any loss suffered by 
        any person acting or refraining from acting as a result of the use of this calculator and/or 
        information obtained through this calculator or the website is accepted by BMW Financial Services. 
        BMW Financial Services does not warrant that the information obtained may be free of errors. 
        Terms and Conditions apply.'
    );
    $block->save();
}

$installer->endSetup();

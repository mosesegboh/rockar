<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Setup
 * @author    Jiraphong witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();

// Ensure new ACL is disabled for existing roles
$installer->disableNewAcl(
    Mage::helper('peppermint_orderstatus')->getAclPathOrderStatusMapping()
);

$installer->endSetup();

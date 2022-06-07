<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Setup
 * @author    Sykander Gul <sykander.gul@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();

// Ensure new ACL is disabled for existing roles
$installer->disableNewAcl(
    Mage::helper('peppermint_sales/order')->getAclPathOtpDownload()
);

$installer->endSetup();

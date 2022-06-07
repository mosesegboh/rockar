<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Gcdm
 * @author    Igors Zhunins <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

$installer->setConfigData(
    'peppermint_gcdm/general/gcdm_login_url',
    'https://customer-i.bmwgroup.com/oneid/#/login'
);

$installer->endSetup();

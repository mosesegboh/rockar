<?php

/**
 * @category     Setup
 * @package      Peppermint_Setup
 * @author       Stefan Lucaci <lucacistefan.alexandru@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();
// Truncate rockar_financing_options_group
Mage::getSingleton('core/resource')->getConnection('core_write')
    ->truncateTable($installer->getTable('rockar_financingoptions/group'));
$installer->endSetup();

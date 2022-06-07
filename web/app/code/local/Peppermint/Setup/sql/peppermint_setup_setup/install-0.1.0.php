<?php

/**
 * @category     Peppermint
 * @package      Peppermint_Setup
 * @author       Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();
$coreConfigDataFallback = ['value' => '[current]:[current]' . PHP_EOL . '[current]:default' . PHP_EOL . 'default:rockar' . PHP_EOL . 'default:peppermint' . PHP_EOL . 'enterprise:default' . PHP_EOL . 'base:default'];
$installer->getConnection()->update($this->getTable('core_config_data'), $coreConfigDataFallback, 'scope="default" and path="design/fallback/fallback"');

$installer->endSetup();

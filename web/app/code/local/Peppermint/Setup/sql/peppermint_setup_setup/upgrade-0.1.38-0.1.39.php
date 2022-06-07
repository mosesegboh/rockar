<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Setup
 * @author    Taras Kapushchak <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();

$coreConfigDataFallback = [
    'value' =>
        '[current]:peppermint' . PHP_EOL
        . '[current]:[current]' . PHP_EOL
        . '[current]:default' . PHP_EOL
        . 'default:rockar' . PHP_EOL
        . 'enterprise:default' . PHP_EOL
        . 'base:default',
];

$installer->getConnection()->update(
    $this->getTable('core_config_data'),
    $coreConfigDataFallback,
    'scope="default" and path="design/fallback/fallback"'
);

$installer->endSetup();

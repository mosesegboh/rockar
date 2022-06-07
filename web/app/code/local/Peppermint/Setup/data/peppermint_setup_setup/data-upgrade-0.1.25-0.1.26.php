<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Setup
 * @author    Ana-Maria Buliga <anamaria.buliga@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();

// Set general region value and region state required
$table = $installer->getTable('core/config_data');
$connection->update($table, ['value' => 1], 'path="general/region/display_all"');
$connection->update($table, ['value' => 'ZA'], 'path="general/region/state_required"');

$installer->endSetup();

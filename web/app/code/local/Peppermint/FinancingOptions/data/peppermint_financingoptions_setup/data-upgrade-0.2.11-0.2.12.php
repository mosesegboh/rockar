<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Jez Horton <jez.horton@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();
$tableName = $installer->getTable('rockar_financing_options');

$data = [
    'cashmini' => '<video width="460" height="260" controls><source src="https://forms.bmw.co.za/SelectVideo/MINI_Cash.mp4" type="video/mp4"></video>',
    'cashmoto' => '<video width="382" height="216" controls><source src="https://forms.bmw.co.za/SelectVideo/Motorrad_Cash.mp4" type="video/mp4"></video>',
    'cashbmw' => '<video width="382" height="216" controls><source src="https://forms.bmw.co.za/SelectVideo/DSP_BMW_Cash_.mp4" type="video/mp4"></video>'
];

if ($connection->isTableExists($tableName) && $connection->tableColumnExists($tableName, 'video')) {
    foreach ($data as $key => $value) {
        $connection->update($tableName, ['video' => $value], ['type=?' => $key]);
    }
}

$installer->endSetup();

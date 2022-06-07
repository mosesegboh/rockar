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
    'cashc' => '<video width="382" height="216" controls><source src="https://forms.bmw.co.za/SelectVideo/Motorrad_Other%20banks.mp4" type="video/mp4"></video>',
    'of1' => '<video width="382" height="216" controls><source src="https://forms.bmw.co.za/SelectVideo/BMW_Other%20banks.mp4" type="video/mp4"></video>',
    'cashm' => '<video width="460" height="260" controls><source src="https://forms.bmw.co.za/SelectVideo/MINI_Other%20banks.mp4" type="video/mp4"></video>',
    'cashb' => '<video width="382" height="" controls><source src="https://forms.bmw.co.za/SelectVideo/BMW_Other%20banks.mp4" type="video/mp4"></video>'
];

if ($connection->isTableExists($tableName) && $connection->tableColumnExists($tableName, 'video')) {
    foreach ($data as $key => $value) {
        $connection->update($tableName, ['video' => $value], ['type=?' => $key]);
    }
}

$installer->endSetup();

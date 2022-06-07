<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Sales
 * @author    Taras Kapushchak <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

/** @var $this Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/** Add 'additional_attributes' attribute for entities */
$entities = [
    'quote_item',
    'order_item',
];

$options = [
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'visible' => true,
    'required' => false,
    'nullable' => true,
];

foreach ($entities as $entity) {
    $installer->addAttribute($entity, 'additional_attributes', $options);
}

$installer->endSetup();

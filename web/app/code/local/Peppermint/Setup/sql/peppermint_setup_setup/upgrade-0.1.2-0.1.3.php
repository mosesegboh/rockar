<?php

/**
 * @category     Setup
 * @package      Peppermint_Setup
 * @author       Catalin Lungu <catalin.lungu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

/** @var Array $searchFor */
$searchFor = ['£', '&pound;'];

/** @var String $replaceWith */
$replaceWith = 'R';

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

/** Tables where pound currency could exists */
$tables = [
    $installer->getTable('core/email_template') => ['template_text'],
    $installer->getTable('cms/block') => ['content'],
    $installer->getTable('cms/page') => ['content'],
    $installer->getTable('eav/attribute_option_value') => ['value'],
    $installer->getTable('rockar_accessories') => ['description'],
    $installer->getTable('rockar_slider_image') => ['image_title', 'slide_text'],
    $installer->getTable('rockar_order_additional_fields') => ['label'],
    $installer->getTable('rockar_financing_options') => ['header', 'footer'],
    $installer->getTable('rockar_financing_options_group') => ['group_description'],
    $installer->getTable('salesrule') => ['name']
];

foreach ($tables as $table => $fields) {
    foreach ($fields as $field) {
        foreach ($searchFor as $symbol) {
            $query =
                'UPDATE ' . $table .
                ' SET ' . $field . ' = REPLACE(' . $field . ',"' . $symbol . '","' . $replaceWith . '")' .
                ' WHERE ' . $field . ' LIKE "%' . $symbol . '%";';
            $installer->run($query);
        }
    }
}

$installer->endSetup();

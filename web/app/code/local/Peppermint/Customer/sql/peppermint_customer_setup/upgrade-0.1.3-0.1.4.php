<?php
/**
* @category  Peppermint
* @package   Peppermint_Customer
* @author    Krists Dadzitis <techteam@rockar.com>
* @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
*/

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

//add attributes for local store name and id
$attributes = [
    'in_store_name' => [
        'type' => 'varchar',
        'input' => 'text',
        'label' => 'Created In (Local Store)',
        'required' => 0,
        'user_defined' => 0,
        'global' => 1,
        'visible' => 1
    ],
    'in_store_id' => [
        'type' => 'int',
        'input' => 'select',
        'label' => 'Created In Local Store Id',
        'required' => 0,
        'user_defined' => 0,
        'global' => 1,
        'visible' => 1
    ]
];

foreach ($attributes as $name => $parameters) {
    $this->addAttribute('customer', $name, $parameters);
}

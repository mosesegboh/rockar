<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Customer
 * @author    Ausma Smite <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

//add attribute for dealer id
$attributes = [
    'dealer_id' => [
        'type' => 'varchar',
        'input' => 'text',
        'label' => 'UserID',
        'required' => 0,
        'user_defined' => 0,
        'global' => 1,
        'visible' => 1
    ]
];

foreach ($attributes as $name => $parameters) {
    $this->addAttribute('customer', $name, $parameters);
}

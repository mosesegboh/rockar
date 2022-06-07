<?php
/* @category  Peppermint
 * @package   Peppermint_NewJourney
 * @author    Taras Kapushchak <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

/**
 * Update Financing Options Variables & Options PDP Variables
 */
$stringsToReplace = [
    'Trade In' => 'Trade-in',
    'Trade-In' => 'Trade-in',
    'TRADE IN' => 'TRADE-IN'
];

$installer = $this;

$installer->startSetup();

$tables = [
    $installer->getTable('rockar_financingoptions/variables') => 'variable_title'
];

foreach ($tables as $table => $field) {
    foreach ($stringsToReplace as $old => $new) {
        $query =
                'UPDATE ' . $table .
                ' SET ' . $field . ' = REPLACE(' . $field . ',"' . $old . '","' . $new . '")' .
                ' WHERE BINARY ' . $field . ' LIKE "%' . $old . '%";';
        $installer->run($query);
    }
}

$installer->endSetup();

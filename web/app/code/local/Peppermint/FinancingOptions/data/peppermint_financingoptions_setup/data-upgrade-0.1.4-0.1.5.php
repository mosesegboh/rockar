<?php
/* @category     Peppermint
 * @package      Peppermint_FinancingOptions
 * @author       Lucian Mesaros <lucian.mesaros@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

/**
 * Update Financing Options Variables & Options PDP Variables
 */
$id = Mage::getModel('rockar_financingoptions/variables')->getCollection()
    ->addFieldToFilter('variable', 'part_exchange')
    ->addFieldToSelect('variable_id')
    ->setPageSize(1)
    ->getFirstItem()
    ->getVariableId();

$stringsToReplace = [
    'Part Exchange' => 'Trade In',
    'PART EXCHANGE' => 'TRADE IN'
];

$installer = $this;

$installer->startSetup();

$tables = [
    $installer->getTable('rockar_financingoptions/options_pdp_variables') => 'variable_title',
    $installer->getTable('rockar_financingoptions/options_variables') => 'variable_title'
];

foreach ($tables as $table => $field) {
    foreach ($stringsToReplace as $old => $new) {
        $query =
                'UPDATE ' . $table .
                ' SET ' . $field . ' = REPLACE(' . $field . ',"' . $old . '","' . $new . '")' .
                ' WHERE variable_id = "' . $id . '" AND BINARY ' . $field . ' LIKE "%' . $old . '%";';
        $installer->run($query);
    }
}

$installer->endSetup();

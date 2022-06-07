<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Remove core shortfall-related variables from payment methods
 */
$variablesToDetachNames = [
    'part_exchange_discount_value_shortfall',
    'part_exchange_discount_value_shortfall_limit',
];
$variablesToDetachIds = [];
$variableModel = Mage::getModel('rockar_financingoptions/variables');

foreach ($variablesToDetachNames as $variable) {
    $variablesToDetachIds[] = $variableModel->load($variable, 'variable')->getId();
    $variableModel->unsetData();
}

unset($variableModel);
$variablesCollection = Mage::getModel('rockar_financingoptions/variables')->getCollection();
$options = Mage::getModel('rockar_financingoptions/options')
    ->getCollection()
    ->addFieldToFilter('is_static', 0);

foreach ($options as $option) {
    $resource = Mage::getModel('rockar_financingoptions/options')->getResource();
    $variables = $resource->getAssignedVariables($option->getId());
    $pdpVariables = $resource->getPdpAssignedVariables($option->getId());

    $variables = array_filter($variables, function($variable) use ($variablesToDetachIds) {
        return !in_array($variable['variable_id'], $variablesToDetachIds);
    });
    $pdpVariables = array_filter($pdpVariables, function($variable) use ($variablesToDetachIds) {
        return !in_array($variable['variable_id'], $variablesToDetachIds);
    });

    $option->setVariablesLinkData($variables)
        ->setPdpVariablesLinkData($pdpVariables)
        ->save();
}

$installer->endSetup();

<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Setup
 * @author    Jiraphong Witthayathanakit <jiraphong.witthayathanakit@rockar.com>
 * @copyright Copyright (c) 2020 Rockar Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();

$websiteIds = Mage::getModel('core/website')->getCollection()
    ->addFieldToSelect('website_id')
    ->addFieldToFilter('code', ['nin' => ['admin', 'demo']])
    ->getColumnValues('website_id');

$customerGroupIds = Mage::getModel('customer/group')->getCollection()
    ->addFieldToSelect('customer_group_id')
    ->getColumnValues('customer_group_id');

$modelAttribute = Mage::helper('peppermint_shortfallallowance')->getModelAttribute();

$modelAttributesValues = Mage::getModel('peppermint_shortfallallowance/adminhtml_system_config_source_attribute_options')
    ->toArray($modelAttribute);

$actionsData = [
    'type' => 'rockar_partexchange/promotions_rule_action_combine',
    'attribute' => null,
    'operator' => null,
    'value' => '1',
    'is_value_processed' => null,
    'aggregator' => 'all',
    'conditions' => [
        [
            'type' => 'rockar_partexchange/promotions_rule_action_product',
            'operator' => '==',
            'is_value_processed' => false
        ]
    ]
];

//new PX promotion rule to save
$dataToSave = [
    'description' => null,
    'scope' => 'shortfall',
    'from_date' => null,
    'to_date' => null,
    'is_active' => '1',
    'conditions_serialized' => serialize([
        'type' => 'rockar_partexchange/promotions_rule_condition_combine',
        'attribute' => null,
        'operator' => null,
        'value' => '1',
        'is_value_processed' => null,
        'aggregator' => 'all'
    ]),
    'stop_rules_processing' => '0',
    'sort_order' => '0',
    'simple_action' => 'by_fixed',
    'customer_group_ids' => $customerGroupIds,
    'website_ids' => $websiteIds
];

$pxPromoRule = Mage::getModel('rockar_partexchange/promotions_rule');
$shortFallData = Mage::getModel('peppermint_shortfallallowance/shortfall_allowance')->getCollection();

foreach ($shortFallData as $value) {
    $models = $value->getModels();

    $actionsData['conditions'][0]['attribute'] = $modelAttribute;
    $actionsData['conditions'][0]['value'] = $models;

    $dataToSave['name'] = ($modelAttributesValues[$models] ?? $models) . ' Shortfall Allowance';
    $dataToSave['actions_serialized'] = serialize($actionsData);
    $dataToSave['discount_amount'] = $value->getShortfallLimit();

    $pxPromoRule->load($dataToSave['name'], 'name');

    if (!$pxPromoRule->getId() && $pxPromoRule->getScope() !== 'shortfall') {
       //need to reload the model each time as unsetData does not clear all data
        Mage::getModel('rockar_partexchange/promotions_rule')->setData($dataToSave)
            ->save();
    }

    $pxPromoRule->unsetData();
}

//Enable shortfall promotions
$installer->setConfigData('partexchange/promotions/enabled_scopes', 'shortfall');

$installer->endSetup();

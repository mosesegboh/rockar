<?php

/**
 * @category     Setup
 * @package      Peppermint_Setup
 * @author       Catalin Lungu <catalin.lungu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

/** @var String $entity */
$entity = 'customer';

$this->startSetup();

/** @var Mage_Eav_Model_Entity_Setup $setup */
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

/** @var int entityTypeId*/
$entityTypeId = $setup->getEntityTypeId($entity);

/** @var int attributeSetId*/
$attributeSetId = $setup->getDefaultAttributeSetId($entityTypeId);

/** @var int attributeGroupId*/
$attributeGroupId = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$attributeName = 'country_of_citizenship';
$setup->addAttribute(
    $entity,
    $attributeName,
    [
        'type'          => 'varchar',
        'label'         => 'Country of Citizeship',
        'input'         => 'select',
        'source'        => 'peppermint_customer/entity_attribute_source_country',
        'required'      => false,
        'visible'       => true,
        'user_defined'  => true
    ]
);

$currentAttribute = Mage::getSingleton('eav/config')->getAttribute($entity, $attributeName);

$setup->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    $attributeName,
    '1000'  //sort_order
);

$currentAttribute->setData('used_in_forms', ['adminhtml_customer'])
    ->setData('is_used_for_customer_segment', true)
    ->save();

$this->endSetup();

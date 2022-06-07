<?php

/**
 * @category     Setup
 * @package      Peppermint_Setup
 * @author       Catalin Lungu <catalin.lungu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

/** @var String $entity */
$entity = 'customer';

/** @var Array $attributes */
$attributes = [
    'south_african_document_type' => [
        'type'          => 'int',
        'label'         => 'Identification Type',
        'input'         => 'select',
        'source'        => 'eav/entity_attribute_source_table',
        'option'        => [
            'values' => [
                'Passport Number',
                'SA ID Number'
            ]
        ]                   
    ],
    'south_african_id_number' => [
        'type'          => 'varchar',
        'label'         => 'Identification Number',
        'input'         => 'text'
    ]
];

$this->startSetup();

/** @var Mage_Eav_Model_Entity_Setup $setup */
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

/** @var int entityTypeId*/
$entityTypeId = $setup->getEntityTypeId($entity);

/** @var int attributeSetId*/
$attributeSetId = $setup->getDefaultAttributeSetId($entityTypeId);

/** @var int attributeGroupId*/
$attributeGroupId = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

foreach ($attributes as $attributeName => $attribute) {
    $setup->addAttribute(
        $entity,
        $attributeName,
        array_merge([
            'required'      => true,
            'visible'       => true,
            'user_defined'  => true,
        ], $attribute)
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
        ->setData('is_system', false)
        ->setData('is_user_defined', true)
        ->setData('is_visible', true)
        ->setData('sort_order', 100)
        ->save();
}
$this->endSetup();

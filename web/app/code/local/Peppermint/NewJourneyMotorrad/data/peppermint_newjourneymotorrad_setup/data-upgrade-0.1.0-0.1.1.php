<?php
/**
 * @category  Peppermint
 * @package   Peppermint_NewJourneyMotorrad
 * @author    Andrian Kogoshvili <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();
$motorradStoreId = Mage::getModel('core/store')->load('motorrad_store_view', 'code')->getId();

$attributesToMap = [
    'engine_capacity' => [
        'text_template' => '%value%<span>cc</span>',
        'label' => 'Capacity',
        'sort_order' => 100
    ],
    'engine_power' => [
        'text_template' => '%value%<span>Kw</span>',
        'label' => 'Engine Performance',
        'sort_order' => 110
    ],
    'torque_only' => [
        'text_template' => '%value%<span>Nm</span>',
        'label' => 'Torque',
        'sort_order' => 120
    ],
    'fuel_type' => [
        'text_template' => '%value%',
        'label' => 'Fuel Type',
        'sort_order' => 30
    ],
    'transmission' => [
        'text_template' => '%value%',
        'label' => 'Transmission',
        'sort_order' => 40
    ]
];

foreach ($attributesToMap as $attributeCode => $podItem) {
    $model = Mage::getModel('rockar_product_pods/item')->getCollection()
        ->addWebsiteFilter($motorradStoreId)
        ->addFieldToFilter('attribute_code', $attributeCode)
        ->setPageSize(1)
        ->setCurPage(1)
        ->getFirstItem();

    $data = [
        'website_id' => $motorradStoreId,
        'attribute_code' => $attributeCode,
        'display' => Rockar_ProductPods_Helper_Data::DISPLAY_OPTION_TEXT,
        'place' => Rockar_ProductPods_Helper_Data::PLACE_OPTION_CAR_FINDER,
        'text_template' => $podItem['text_template'],
        'label' => $podItem['label'],
        'sort_order' => $podItem['sort_order']
    ];

    $model->addData($data);
    $model->save();
}

$installer->endSetup();

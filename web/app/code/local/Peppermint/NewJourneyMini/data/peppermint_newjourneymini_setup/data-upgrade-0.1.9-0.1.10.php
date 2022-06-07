<?php
/**
 * @category  Peppermint
 * @package   Peppermint_NewJourneyMini
 * @author    Andrian Kogoshvili<techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();
$miniStoreId = Mage::getModel('core/store')->load('mini_store_view', 'code')->getId();

$attributesToMap = [
    'n_0_to_100' => [
        'text_template' => '%value%<span>S</span>',
        'label' => 'Acceleration',
        'sort_order' => 100
    ],
    'engine_power' => [
        'text_template' => '%value%<span>Kw</span>',
        'label' => 'Performance',
        'sort_order' => 110
    ],
    'ec_combined_mpg' => [
        'text_template' => '%value%<span>l/100km</span>',
        'label' => 'Fuel Consumption (combined)',
        'sort_order' => 120
    ]
];

foreach ($attributesToMap as $attributeCode => $podItem) {
    $model = Mage::getModel('rockar_product_pods/item')->getCollection()
        ->addWebsiteFilter($miniStoreId)
        ->addFieldToFilter('attribute_code', $attributeCode)
        ->setPageSize(1)
        ->setCurPage(1)
        ->getFirstItem();

    $data = [
        'website_id' => $miniStoreId,
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

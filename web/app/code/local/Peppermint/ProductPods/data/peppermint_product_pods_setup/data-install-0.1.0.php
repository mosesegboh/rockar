<?php
/**
 * @category  Peppermint
 * @package   Peppermint_ProductPods
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();

$productPodsItems = [
    'bmw' => [
        'fuel_type' => ['text_template' => '%value%', 'sort_order' => 10],
        'km' => ['text_template' => '%value%', 'sort_order' => 20],
        'vehicle_condition' => ['text_template' => '%value%', 'sort_order' => 30],
        'transmission' => ['text_template' => '%value%', 'sort_order' => 40]
    ],
    'mini' => [
        'fuel_type' => ['text_template' => '%value%', 'sort_order' => 10],
        'km' => ['text_template' => '%value%', 'sort_order' => 20],
        'vehicle_condition' => ['text_template' => '%value%', 'sort_order' => 30],
        'transmission' => ['text_template' => '%value%', 'sort_order' => 40]
    ],
    'motorrad' => [
        'km' => ['text_template' => '%value%', 'sort_order' => 10],
        'vehicle_condition' => ['text_template' => '%value%', 'sort_order' => 20]
    ]
];

foreach ($productPodsItems as $websiteCode => $attributesToMap) {
    $website = Mage::getModel('core/website')->getCollection()
        ->addFieldToFilter('code', $websiteCode)
        ->setCurPage(1)
        ->setPageSize(1)
        ->getFirstItem();

    $websiteId = $website->getId();

    foreach ($attributesToMap as $attributeCode => $podItem) {
        $model = Mage::getModel('rockar_product_pods/item')->getCollection()
            ->addWebsiteFilter($websiteId)
            ->addFieldToFilter('attribute_code', $attributeCode)
            ->setPageSize(1)
            ->setCurPage(1)
            ->getFirstItem();

        $data = [
            'website_id' => $websiteId,
            'attribute_code' => $attributeCode,
            'display' => Rockar_ProductPods_Helper_Data::DISPLAY_OPTION_ICON_TEXT,
            'place' => Rockar_ProductPods_Helper_Data::PLACE_OPTION_CAR_FINDER,
            'text_template' => $podItem['text_template'],
            'sort_order' => $podItem['sort_order'],
        ];

        $model->addData($data);
        $model->save();
    }
}

$installer->endSetup();

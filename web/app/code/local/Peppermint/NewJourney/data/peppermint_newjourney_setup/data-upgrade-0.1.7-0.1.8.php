<?php
/**
 * @category  Peppermint
 * @package   Peppermint_ProductPods
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

$installer = $this;
$installer->startSetup();

$productPodsItems = [
    'bmw' => [
        'n_0_to_100' => [
            'text_template' => '%value%<span>sec</span>',
            'label' => '0-100 km/h',
            'sort_order' => 100,
        ],
        'engine_power' => [
            'text_template' => '%value%<span>kW</span>',
            'label' => 'Engine Performance',
            'sort_order' => 110,
        ],
        'ec_combined_mpg' => [
            'text_template' => '%value%<span>l/100km</span>',
            'label' => 'Fuel Consumption (combined)',
            'sort_order' => 120,
        ],
    ],
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
            'display' => Rockar_ProductPods_Helper_Data::DISPLAY_OPTION_TEXT,
            'place' => Rockar_ProductPods_Helper_Data::PLACE_OPTION_CAR_FINDER,
            'text_template' => $podItem['text_template'],
            'label' => $podItem['label'],
            'sort_order' => $podItem['sort_order'],
        ];

        $model->addData($data);
        $model->save();
    }
}

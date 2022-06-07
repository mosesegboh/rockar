<?php

/**
 * @category  Peppermint
 * @package   Peppermint_Catalog
 * @author    Adrian Grigorita <adrian.grigorita@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
class Peppermint_Catalog_Helper_Attributes extends Rockar_Catalog_Helper_Attributes
{
    /**
     * Product Pods and PDP Attributes label map
     */
    const PRODUCT_ATTRIBUTE_LABEL_MAP = [
        'vehicle_condition' => [
            'used' => 'Demo'
        ]
    ];

    /**
     * Parse specs view table and extract data in needed format
     *
     * @param $technicalSpecifications
     * @return array
     */
    public function parseTechnicalSpecifications($technicalSpecifications)
    {
        $result = [];
        foreach ($technicalSpecifications as $options) {
            if (is_array($options) && count($options) && isset($options['table'])) {
                foreach ($options['table'] as $option) {
                    if (isset($option['items']) && count($option['items'])) {
                        $resultItem = [
                            'title' => $option['tableHeading']['heading'] ?? '',
                            'subtitle' => $option['title'] ?? '',
                            'type' => $option['type'],
                            'items' => []
                        ];
                        foreach ($option['items'] as $itemsSection) {
                            if (is_array($itemsSection)) {
                                foreach ($itemsSection as $key => $item) {
                                    if ($key !== 'heading') {
                                        $resultItem['items'][($key + 1)] = [
                                            'title' => $option['tableHeading'][$key]['title'] ?? '',
                                            'value' => $item
                                        ];
                                    }
                                }
                            }
                        }
                        $result[] = $resultItem;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Parse standard features and extract data in needed format
     *
     * @param $product
     * @return array
     */
    protected function _getStandardFeaturesAsArray(Mage_Catalog_Model_Product $product)
    {
        if ($product && $product->getId()) {
            $result = [];
            $standardFeatures = Mage::helper('rockar_all')->jsonDecode($product->getStandardFeatures());
            foreach ($standardFeatures as $name => $features) {
                // format pretty name for section name in case is with underscore
                $newSection = ucwords(str_replace('_', ' ', $name));
                if (is_array($features)) {
                    foreach ($features as $feature) {
                        $result[$newSection][] = $feature['description'];
                    }
                }
            }

            return $result;
        }

        return [];
    }

    /**
     * Map product Attributes
     *
     * @param $product
     * @return array
     */

    public function mapCarAttributes($product)
    {
        /** @var Rockar_ProductPods_Helper_Data $itemHelper */
        $itemHelper = Mage::helper('rockar_product_pods');
        $carAttributesTemplates = $itemHelper->prepareCarAttributes();

        $carAttributes = [];

        foreach ($carAttributesTemplates as $attributeCode => $carAttributesTemplate) {
            if ($product->getResource()->getAttribute($attributeCode)->getFrontendInput() === 'select') {
                $value = $product->getAttributeText($attributeCode);
            } else {
                $value = $product->getData($attributeCode);
            }

            if ($value) {
                $value = self::PRODUCT_ATTRIBUTE_LABEL_MAP[$attributeCode][strtolower($value)] ?? $value;
                $carAttribute = $carAttributesTemplate;
                $carAttribute['value'] = preg_replace('/\(.*\)/', '', $itemHelper->prepareCarAttributesTextValue($value, $carAttributesTemplate['text_template']));
                $carAttribute['code'] = $attributeCode;
                $carAttributes[$attributeCode] = $carAttribute;
            }
        }

        return $carAttributes;
    }
}
